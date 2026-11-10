<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DatabaseArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_shop_owner_products_are_global_but_not_automatically_listed(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $otherOwner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        $product = Product::factory()->for($owner, 'creator')->create();

        $this->assertTrue($owner->products->contains($product));
        $this->assertTrue(Product::query()->whereKey($product->id)->exists());
        $this->assertCount(0, $shop->listings);
        $this->assertSame(User::ROLE_SHOP_OWNER, $otherOwner->fresh()->role);

        $otherShop = Shop::factory()->for($otherOwner, 'owner')->create();
        $shop->products()->attach($product->id, ['price' => '12.00', 'status' => 'active']);
        $otherShop->products()->attach($product->id, ['price' => '14.00', 'status' => 'active']);
        $this->assertCount(2, $product->fresh()->shops);
    }

    public function test_listing_overrides_fall_back_to_global_product_fields_and_price_is_decimal(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        $product = Product::factory()->for($owner, 'creator')->create([
            'name' => 'Global title',
            'description' => 'Global description',
            'thumbnail' => 'products/global.jpg',
        ]);

        $listing = ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'price' => '19.90',
            'status' => 'active',
        ]);

        $this->assertSame('Global title', $listing->name);
        $this->assertSame('Global description', $listing->description);
        $this->assertSame('products/global.jpg', $listing->thumbnail);
        $this->assertSame('19.90', $listing->price);

        $listing->update(['name' => 'Shop-specific title', 'description' => 'Local copy']);
        $this->assertSame('Shop-specific title', $listing->fresh()->name);
        $this->assertSame('Global title', $product->fresh()->name);
    }

    public function test_same_product_cannot_be_assigned_twice_to_one_shop(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        $product = Product::factory()->for($owner, 'creator')->create();
        $attributes = ['shop_id' => $shop->id, 'product_id' => $product->id, 'price' => '5.00', 'status' => 'active'];

        ShopProduct::create($attributes);
        $this->expectException(QueryException::class);
        ShopProduct::create($attributes);
    }

    public function test_deleting_a_shop_cascades_its_listings(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create();
        $product = Product::factory()->for($owner, 'creator')->create();
        ShopProduct::create(['shop_id' => $shop->id, 'product_id' => $product->id, 'price' => '7.25', 'status' => 'active']);

        $shop->delete();

        $this->assertDatabaseMissing('shop_products', ['shop_id' => $shop->id, 'product_id' => $product->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
