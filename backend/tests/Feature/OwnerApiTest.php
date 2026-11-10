<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerApiTest extends TestCase
{
    use DatabaseTransactions;

    private function signInOwner(): User
    {
        $owner = User::factory()->shopOwner()->create();
        $this->actingAs($owner, 'sanctum');

        return $owner;
    }

    public function test_owner_routes_require_shop_owner_role(): void
    {
        $this->getJson('/api/owner/dashboard')->assertUnauthorized();

        $admin = User::factory()->admin()->create();
        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/owner/dashboard')->assertForbidden();
    }

    public function test_owner_shops_are_scoped_and_catalog_is_shared_across_creators(): void
    {
        $owner = $this->signInOwner();
        $otherOwner = User::factory()->shopOwner()->create();
        $admin = User::factory()->admin()->create();
        $ownedShop = Shop::factory()->for($owner, 'owner')->create();
        $otherShop = Shop::factory()->for($otherOwner, 'owner')->create();
        $adminProduct = Product::factory()->for($admin, 'creator')->create(['name' => 'Admin Shared Product']);
        $otherOwnerProduct = Product::factory()->for($otherOwner, 'creator')->create(['name' => 'Owner Shared Product']);

        $this->getJson('/api/owner/shops')->assertOk()->assertJsonPath('data.0.id', $ownedShop->id);
        $shops = $this->getJson('/api/owner/shops?per_page=100')->assertOk();
        $this->assertSame([$ownedShop->id], collect($shops->json('data'))->pluck('id')->all());

        $catalog = $this->getJson('/api/owner/catalog?per_page=100')->assertOk();
        $catalogIds = collect($catalog->json('data'))->pluck('id')->all();
        $this->assertContains($adminProduct->id, $catalogIds);
        $this->assertContains($otherOwnerProduct->id, $catalogIds);

        $this->getJson('/api/owner/catalog/'.$adminProduct->id)
            ->assertOk()
            ->assertJsonPath('data.creator.role', User::ROLE_ADMIN)
            ->assertJsonMissingPath('data.creator.email');
        $this->getJson('/api/owner/catalog?q=Owner%20Shared&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $otherOwnerProduct->id)
            ->assertJsonPath('meta.per_page', 1);

        $dashboard = $this->getJson('/api/owner/dashboard')->assertOk();
        $this->assertSame(1, $dashboard->json('data.shops.total'));
        $this->assertSame(0, $dashboard->json('data.products_created'));
        $this->assertSame(Product::count(), $dashboard->json('data.catalog_products'));
    }

    public function test_every_listing_route_rejects_shops_owned_by_another_user(): void
    {
        $this->signInOwner();
        $otherOwner = User::factory()->shopOwner()->create();
        $foreignShop = Shop::factory()->for($otherOwner, 'owner')->create();
        $product = Product::factory()->for($otherOwner, 'creator')->create();
        $listing = ShopProduct::create([
            'shop_id' => $foreignShop->id,
            'product_id' => $product->id,
            'price' => '10.00',
            'status' => 'active',
        ]);

        $this->getJson('/api/owner/shops/'.$foreignShop->id.'/products')->assertNotFound();
        $this->postJson('/api/owner/shops/'.$foreignShop->id.'/products', [
            'product_id' => $product->id,
            'price' => '11.00',
        ])->assertNotFound();
        $this->putJson('/api/owner/shops/'.$foreignShop->id.'/products/'.$listing->id, ['price' => '12.00'])->assertNotFound();
        $this->deleteJson('/api/owner/shops/'.$foreignShop->id.'/products/'.$listing->id)->assertNotFound();

        $this->assertDatabaseHas('shop_products', ['id' => $listing->id, 'shop_id' => $foreignShop->id, 'price' => '10.00']);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_owner_can_add_shared_products_override_listing_fields_and_remove_only_the_listing(): void
    {
        Storage::fake('public');
        $owner = $this->signInOwner();
        $admin = User::factory()->admin()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        $globalProduct = Product::factory()->for($admin, 'creator')->create([
            'name' => 'Original Global Name',
            'description' => 'Original global description',
            'thumbnail' => 'products/thumbnails/global.png',
        ]);
        Storage::disk('public')->put($globalProduct->thumbnail, 'global-image');

        $added = $this->postJson('/api/owner/shops/'.$shop->id.'/products', [
            'product_id' => $globalProduct->id,
            'price' => '24.50',
            'name' => 'My Shop Name',
            'description' => 'My shop description',
            'thumbnail' => UploadedFile::fake()->image('shop-item.png'),
        ])->assertCreated()->assertJsonPath('data.name', 'My Shop Name')
            ->assertJsonPath('data.description', 'My shop description')
            ->assertJsonPath('data.price', '24.50');

        $listingId = $added->json('data.id');
        $listingThumbnail = $added->json('data.thumbnail');
        Storage::disk('public')->assertExists($listingThumbnail);
        $this->assertDatabaseHas('products', [
            'id' => $globalProduct->id,
            'name' => 'Original Global Name',
            'description' => 'Original global description',
            'thumbnail' => 'products/thumbnails/global.png',
        ]);

        $this->postJson('/api/owner/shops/'.$shop->id.'/products', [
            'product_id' => $globalProduct->id,
            'price' => '25.00',
        ])->assertConflict()->assertJsonPath('code', 'DUPLICATE_SHOP_LISTING');
        $this->getJson('/api/owner/dashboard')->assertOk()->assertJsonPath('data.listings.total', 1);

        $this->putJson('/api/owner/shops/'.$shop->id.'/products/'.$listingId, [
            'price' => '26.75',
            'name' => null,
            'description' => null,
            'remove_thumbnail' => true,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Original Global Name')
            ->assertJsonPath('data.description', 'Original global description')
            ->assertJsonPath('data.thumbnail', 'products/thumbnails/global.png')
            ->assertJsonPath('data.price', '26.75');
        Storage::disk('public')->assertMissing($listingThumbnail);

        $this->getJson('/api/owner/shops/'.$shop->id.'/products?q=Original%20Global&per_page=1')
            ->assertOk()->assertJsonPath('data.0.id', $listingId)->assertJsonPath('meta.per_page', 1);

        $this->deleteJson('/api/owner/shops/'.$shop->id.'/products/'.$listingId)->assertNoContent();
        $this->assertDatabaseMissing('shop_products', ['id' => $listingId]);
        $this->assertDatabaseHas('products', ['id' => $globalProduct->id, 'name' => 'Original Global Name']);
        Storage::disk('public')->assertExists('products/thumbnails/global.png');
    }

    public function test_owner_created_global_product_is_immediately_shared_and_only_creator_can_update_it(): void
    {
        Storage::fake('public');
        $owner = $this->signInOwner();
        $created = $this->postJson('/api/owner/products', [
            'name' => 'Owner Catalog Product',
            'slug' => 'owner-catalog-product-'.fake()->unique()->numerify('###'),
            'description' => 'Shared product description',
            'thumbnail' => UploadedFile::fake()->image('owner-product.webp'),
        ])->assertCreated();

        $productId = $created->json('data.id');
        $this->assertSame($owner->id, $created->json('data.created_by'));
        $this->assertSame(0, Product::findOrFail($productId)->listings()->count());
        $this->assertStringStartsWith('products/thumbnails/', $created->json('data.thumbnail'));
        Storage::disk('public')->assertExists($created->json('data.thumbnail'));
        $this->getJson('/api/owner/catalog?q=Owner%20Catalog%20Product')
            ->assertOk()->assertJsonPath('data.0.id', $productId);

        $otherOwner = User::factory()->shopOwner()->create();
        $otherProduct = Product::factory()->for($otherOwner, 'creator')->create();
        $this->putJson('/api/owner/products/'.$otherProduct->id, ['name' => 'Should not change'])->assertNotFound();
        $this->assertDatabaseHas('products', ['id' => $otherProduct->id, 'name' => $otherProduct->name]);

        $this->putJson('/api/owner/products/'.$productId, [
            'name' => 'Updated Owner Product',
            'description' => 'Updated globally by its creator',
            'remove_thumbnail' => true,
        ])->assertOk()->assertJsonPath('data.name', 'Updated Owner Product')->assertJsonPath('data.thumbnail', null);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'created_by' => $owner->id,
            'name' => 'Updated Owner Product',
            'description' => 'Updated globally by its creator',
            'thumbnail' => null,
        ]);
        Storage::disk('public')->assertMissing($created->json('data.thumbnail'));
    }

    public function test_cross_owner_catalog_listing_prices_thumbnails_and_shop_isolation_work_end_to_end(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $ownerA = User::factory()->shopOwner()->create();
        $ownerB = User::factory()->shopOwner()->create();
        $shopA = Shop::factory()->for($ownerA, 'owner')->create(['status' => 'active']);
        $shopB = Shop::factory()->for($ownerB, 'owner')->create(['status' => 'active']);

        $this->actingAs($admin, 'sanctum');
        $globalResponse = $this->postJson('/api/admin/products', [
            'name' => 'Cross Owner Test Product',
            'slug' => 'cross-owner-test-product-'.fake()->unique()->numerify('###'),
            'description' => 'Shared global description',
            'thumbnail' => UploadedFile::fake()->image('global.png'),
        ])->assertCreated();
        $productId = $globalResponse->json('data.id');
        $globalThumbnail = $globalResponse->json('data.thumbnail');

        $this->actingAs($ownerA, 'sanctum');
        $this->getJson('/api/owner/catalog?q=Cross%20Owner%20Test')->assertOk()
            ->assertJsonPath('data.0.id', $productId);
        $listingA = $this->postJson('/api/owner/shops/'.$shopA->id.'/products', [
            'product_id' => $productId,
            'price' => '80000.00',
            'name' => 'Shop A product name',
            'thumbnail' => UploadedFile::fake()->image('shop-a.png'),
        ])->assertCreated()->json('data');
        $shopAThumbnail = $listingA['thumbnail'];

        $this->actingAs($ownerB, 'sanctum');
        $this->getJson('/api/owner/catalog?q=Cross%20Owner%20Test')->assertOk()
            ->assertJsonPath('data.0.id', $productId);
        $listingB = $this->postJson('/api/owner/shops/'.$shopB->id.'/products', [
            'product_id' => $productId,
            'price' => '85000.00',
            'name' => 'Shop B product name',
            'thumbnail' => UploadedFile::fake()->image('shop-b.png'),
        ])->assertCreated()->json('data');
        $shopBThumbnail = $listingB['thumbnail'];
        $this->assertNotSame($shopAThumbnail, $shopBThumbnail);

        $this->actingAs($ownerA, 'sanctum');
        $this->putJson('/api/owner/shops/'.$shopA->id.'/products/'.$listingA['id'], [
            'price' => '81000.00',
            'thumbnail' => UploadedFile::fake()->image('shop-a-updated.png'),
        ])->assertOk()->assertJsonPath('data.price', '81000.00');
        $updatedShopAThumbnail = ShopProduct::findOrFail($listingA['id'])->getRawOriginal('thumbnail');
        $this->assertNotSame($shopAThumbnail, $updatedShopAThumbnail);
        $this->assertSame('85000.00', ShopProduct::findOrFail($listingB['id'])->getRawOriginal('price'));
        $this->assertSame($shopBThumbnail, ShopProduct::findOrFail($listingB['id'])->getRawOriginal('thumbnail'));
        Storage::disk('public')->assertExists($shopBThumbnail);
        Storage::disk('public')->assertExists($updatedShopAThumbnail);

        $this->getJson('/api/owner/shops/'.$shopB->id.'/products')->assertNotFound();
        $this->putJson('/api/owner/shops/'.$shopA->id.'/products/'.$listingB['id'], ['price' => '1.00'])->assertNotFound();
        $this->deleteJson('/api/owner/shops/'.$shopA->id.'/products/'.$listingB['id'])->assertNotFound();

        $this->getJson('/api/public/shops/'.$shopA->slug.'/products')->assertOk()
            ->assertJsonPath('data.0.product_id', $productId)
            ->assertJsonPath('data.0.price', '81000.00')
            ->assertJsonPath('data.0.name', 'Shop A product name');
        $this->getJson('/api/public/shops/'.$shopB->slug.'/products')->assertOk()
            ->assertJsonPath('data.0.product_id', $productId)
            ->assertJsonPath('data.0.price', '85000.00')
            ->assertJsonPath('data.0.name', 'Shop B product name');

        $ownerProduct = $this->postJson('/api/owner/products', [
            'name' => 'Owner A New Shared Product',
            'slug' => 'owner-a-new-shared-product-'.fake()->unique()->numerify('###'),
            'description' => 'Created by owner A and shared globally.',
        ])->assertCreated()->json('data');
        $this->actingAs($ownerB, 'sanctum');
        $this->getJson('/api/owner/catalog?q=Owner%20A%20New%20Shared')->assertOk()
            ->assertJsonPath('data.0.id', $ownerProduct['id']);

        $this->actingAs($ownerA, 'sanctum');
        $this->deleteJson('/api/owner/shops/'.$shopA->id.'/products/'.$listingA['id'])->assertNoContent();
        $this->assertDatabaseMissing('shop_products', ['id' => $listingA['id']]);
        $this->assertDatabaseHas('products', ['id' => $productId, 'thumbnail' => $globalThumbnail]);
        $this->assertDatabaseHas('shop_products', ['id' => $listingB['id'], 'shop_id' => $shopB->id, 'price' => '85000.00']);
        $this->getJson('/api/public/shops/'.$shopA->slug.'/products/'.$productId)->assertNotFound();
        $this->getJson('/api/public/shops/'.$shopB->slug.'/products/'.$productId)->assertOk();
    }

    public function test_owner_product_and_listing_inputs_validate_image_and_price_rules(): void
    {
        $owner = $this->signInOwner();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        $product = Product::factory()->for($owner, 'creator')->create();

        $this->postJson('/api/owner/products', [
            'name' => 'Invalid Image Product',
            'slug' => 'invalid-image-product',
            'thumbnail' => UploadedFile::fake()->create('unsafe.svg', 10, 'image/svg+xml'),
        ])->assertUnprocessable()->assertJsonValidationErrors('thumbnail');

        $this->postJson('/api/owner/shops/'.$shop->id.'/products', [
            'product_id' => $product->id,
            'price' => '-1.00',
        ])->assertUnprocessable()->assertJsonValidationErrors('price');
    }
}
