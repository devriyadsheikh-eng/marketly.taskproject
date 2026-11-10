<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use DatabaseTransactions;

    private function signInAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    public function test_admin_apis_require_admin_role(): void
    {
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();

        $owner = User::factory()->shopOwner()->create();
        $this->withToken($owner->createToken('test')->plainTextToken)
            ->getJson('/api/admin/dashboard')->assertForbidden();
    }

    public function test_dashboard_returns_shop_owner_product_and_listing_statistics(): void
    {
        $this->signInAdmin();

        $this->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'shops' => ['total', 'active', 'inactive'],
                'users' => ['total', 'active', 'inactive'],
                'shop_owners' => ['total', 'active', 'inactive'],
                'products' => ['total'],
                'shop_listings' => ['total', 'active', 'inactive'],
            ]]);
    }

    public function test_admin_can_create_list_update_and_safely_delete_shops(): void
    {
        Storage::fake('public');
        $admin = $this->signInAdmin();
        $owner = User::factory()->shopOwner()->create();
        $newOwner = User::factory()->shopOwner()->create();

        $this->postJson('/api/admin/shops', [
            'owner_id' => $admin->id,
            'name' => 'Invalid Owner Shop',
            'slug' => 'invalid-owner-shop',
        ])->assertUnprocessable()->assertJsonValidationErrors('owner_id');

        $created = $this->postJson('/api/admin/shops', [
            'owner_id' => $owner->id,
            'name' => 'Test Store',
            'slug' => 'test-store-'.fake()->unique()->numerify('###'),
            'description' => 'Test shop description',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertCreated();

        $shopId = $created->json('data.id');
        $this->assertDatabaseHas('shops', ['id' => $shopId, 'owner_id' => $owner->id, 'status' => 'active']);
        $this->assertStringStartsWith('shops/logos/', $created->json('data.logo'));
        Storage::disk('public')->assertExists($created->json('data.logo'));

        $this->getJson('/api/admin/shops?q=Test%20Store')->assertOk()->assertJsonPath('data.0.id', $shopId);
        $this->getJson('/api/admin/shops/'.$shopId)->assertOk()->assertJsonPath('data.owner.id', $owner->id);

        $this->putJson('/api/admin/shops/'.$shopId, [
            'owner_id' => $newOwner->id,
            'status' => 'inactive',
            'remove_logo' => true,
        ])->assertOk()->assertJsonPath('data.status', 'inactive')->assertJsonPath('data.logo', null);
        $this->assertDatabaseHas('shops', ['id' => $shopId, 'owner_id' => $newOwner->id, 'status' => 'inactive', 'logo' => null]);
        Storage::disk('public')->assertMissing($created->json('data.logo'));
        $this->putJson('/api/admin/shops/'.$shopId, ['status' => 'active'])->assertOk()->assertJsonPath('data.status', 'active');
        $this->putJson('/api/admin/shops/'.$shopId, ['status' => 'inactive'])->assertOk()->assertJsonPath('data.status', 'inactive');

        $product = Product::factory()->for($owner, 'creator')->create();
        $listingThumbnail = 'shop-products/thumbnails/override.jpg';
        Storage::disk('public')->put($listingThumbnail, 'image');
        ShopProduct::create([
            'shop_id' => $shopId,
            'product_id' => $product->id,
            'price' => '12.50',
            'thumbnail' => $listingThumbnail,
            'status' => 'active',
        ]);

        $this->deleteJson('/api/admin/shops/'.$shopId)->assertConflict()->assertJsonPath('code', 'SHOP_HAS_LISTINGS');
        $this->assertDatabaseHas('shops', ['id' => $shopId]);

        $this->deleteJson('/api/admin/shops/'.$shopId, ['delete_listings' => true])->assertNoContent();
        $this->assertDatabaseMissing('shops', ['id' => $shopId]);
        $this->assertDatabaseMissing('shop_products', ['shop_id' => $shopId]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($listingThumbnail);
    }

    public function test_shop_logo_upload_rejects_unsupported_file_types(): void
    {
        $this->signInAdmin();
        $owner = User::factory()->shopOwner()->create();

        $this->postJson('/api/admin/shops', [
            'owner_id' => $owner->id,
            'name' => 'Bad Upload Shop',
            'slug' => 'bad-upload-shop',
            'logo' => UploadedFile::fake()->create('logo.svg', 20, 'image/svg+xml'),
        ])->assertUnprocessable()->assertJsonValidationErrors('logo');

        $this->postJson('/api/admin/shops', [
            'owner_id' => $owner->id,
            'name' => 'Oversized Upload Shop',
            'slug' => 'oversized-upload-shop',
            'logo' => UploadedFile::fake()->image('large.png')->size(2049),
        ])->assertUnprocessable()->assertJsonValidationErrors('logo');
    }

    public function test_admin_can_create_list_update_deactivate_and_safely_delete_shop_owners(): void
    {
        $this->signInAdmin();

        $created = $this->postJson('/api/admin/users', [
            'name' => 'New Owner',
            'email' => 'new-owner-'.fake()->unique()->numerify('####').'@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ])->assertCreated();

        $ownerId = $created->json('data.id');
        $owner = User::findOrFail($ownerId);
        $this->assertSame(User::ROLE_SHOP_OWNER, $owner->role);
        $this->assertTrue(Hash::check('strong-password', $owner->password));
        $this->assertArrayNotHasKey('password', $created->json('data'));

        $this->getJson('/api/admin/users?q=New%20Owner')->assertOk()->assertJsonPath('data.0.id', $ownerId);
        $this->getJson('/api/admin/users/'.$ownerId)->assertOk()->assertJsonPath('data.email', $owner->email);
        $owner->createToken('old-token');

        $this->putJson('/api/admin/users/'.$ownerId, [
            'is_active' => false,
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ])->assertOk()->assertJsonPath('data.is_active', false);
        $this->assertTrue(Hash::check('replacement-password', $owner->fresh()->password));
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $ownerId]);
        $this->getJson('/api/admin/users?q='.$owner->email.'&is_active=false')
            ->assertOk()->assertJsonPath('meta.total', 1);

        $this->putJson('/api/admin/users/'.$ownerId, ['is_active' => true])->assertOk()->assertJsonPath('data.is_active', true);
        $this->getJson('/api/admin/users?q='.$owner->email.'&is_active=true')
            ->assertOk()->assertJsonPath('meta.total', 1);
        $this->putJson('/api/admin/users/'.$ownerId, ['is_active' => false])->assertOk()->assertJsonPath('data.is_active', false);

        $shop = Shop::factory()->for($owner, 'owner')->create();
        $this->deleteJson('/api/admin/users/'.$ownerId)->assertConflict()->assertJsonPath('code', 'SHOP_OWNER_HAS_DEPENDENCIES');
        $this->assertDatabaseHas('users', ['id' => $ownerId]);

        $shop->delete();
        $this->deleteJson('/api/admin/users/'.$ownerId)->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $ownerId]);
    }

    public function test_admin_cannot_assign_admin_role_through_shop_owner_endpoint(): void
    {
        $this->signInAdmin();

        $this->postJson('/api/admin/users', [
            'name' => 'Forged Admin',
            'email' => 'forged-admin@example.com',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'role' => 'admin',
        ])->assertUnprocessable()->assertJsonValidationErrors('role');
    }

    public function test_admin_can_crud_global_products_and_deletion_is_blocked_while_listed(): void
    {
        Storage::fake('public');
        $admin = $this->signInAdmin();

        $created = $this->postJson('/api/admin/products', [
            'name' => 'Admin Product',
            'slug' => 'admin-product-'.fake()->unique()->numerify('###'),
            'description' => 'A catalog item',
            'thumbnail' => UploadedFile::fake()->image('item.jpg'),
        ])->assertCreated();

        $productId = $created->json('data.id');
        $product = Product::findOrFail($productId);
        $this->assertSame($admin->id, $product->created_by);
        $this->assertStringStartsWith('products/thumbnails/', $product->thumbnail);
        Storage::disk('public')->assertExists($product->thumbnail);

        $this->getJson('/api/admin/products?q=Admin%20Product')->assertOk()->assertJsonPath('data.0.id', $productId);
        $this->getJson('/api/admin/products/'.$productId)->assertOk()->assertJsonPath('data.creator.id', $admin->id);

        $this->putJson('/api/admin/products/'.$productId, [
            'name' => 'Updated Product',
            'remove_thumbnail' => true,
        ])->assertOk()->assertJsonPath('data.name', 'Updated Product')->assertJsonPath('data.thumbnail', null);
        Storage::disk('public')->assertMissing($product->thumbnail);

        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $productId, 'price' => '6.25', 'status' => 'active']);

        $this->deleteJson('/api/admin/products/'.$productId)
            ->assertConflict()
            ->assertJsonPath('code', 'PRODUCT_HAS_LISTINGS')
            ->assertJsonPath('listing_count', 1);
        $this->assertDatabaseHas('products', ['id' => $productId]);

        $this->deleteJson('/api/admin/products/'.$productId)->assertConflict();
        $listing = ShopProduct::where('product_id', $productId)->firstOrFail();
        $listing->delete();
        $this->deleteJson('/api/admin/products/'.$productId)->assertNoContent();
        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_admin_can_view_paginated_shop_listings_with_filters(): void
    {
        $this->signInAdmin();
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        $product = Product::factory()->for($owner, 'creator')->create(['name' => 'Listed Product']);
        ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'name' => 'Shop Specific Name',
            'price' => '33.40',
            'status' => 'active',
        ]);

        $this->getJson('/api/admin/shop-listings?q=Shop%20Specific&shop_id='.$shop->id.'&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Shop Specific Name')
            ->assertJsonPath('data.0.shop.id', $shop->id)
            ->assertJsonPath('data.0.product.id', $product->id)
            ->assertJsonStructure(['meta' => ['current_page', 'per_page', 'total', 'last_page']]);
    }
}
