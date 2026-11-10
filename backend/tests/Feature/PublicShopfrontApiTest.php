<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicShopfrontApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_shop_directory_and_product_routes_only_expose_active_assigned_records(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $activeShop = Shop::factory()->for($owner, 'owner')->create(['status' => 'active']);
        $inactiveShop = Shop::factory()->for($owner, 'owner')->create(['status' => 'inactive']);

        $assigned = Product::factory()->for($owner, 'creator')->create([
            'name' => 'Assigned Global Product',
            'description' => 'Global description',
            'thumbnail' => 'products/thumbnails/global.jpg',
        ]);
        $inactiveListingProduct = Product::factory()->for($owner, 'creator')->create();
        $unassignedProduct = Product::factory()->for($owner, 'creator')->create();
        $otherShopProduct = Product::factory()->for($owner, 'creator')->create();

        $activeListing = ShopProduct::create([
            'shop_id' => $activeShop->id,
            'product_id' => $assigned->id,
            'price' => '18.50',
            'status' => 'active',
        ]);
        ShopProduct::create([
            'shop_id' => $activeShop->id,
            'product_id' => $inactiveListingProduct->id,
            'price' => '22.00',
            'status' => 'inactive',
        ]);
        ShopProduct::create([
            'shop_id' => $inactiveShop->id,
            'product_id' => $inactiveListingProduct->id,
            'price' => '20.00',
            'status' => 'active',
        ]);
        ShopProduct::create([
            'shop_id' => Shop::factory()->for($owner, 'owner')->create()->id,
            'product_id' => $otherShopProduct->id,
            'price' => '30.00',
            'status' => 'active',
        ]);

        $allActiveShops = $this->getJson('/api/public/shops?per_page=100')->assertOk();
        $activeShopIds = collect($allActiveShops->json('data'))->pluck('id')->all();
        $this->assertContains($activeShop->id, $activeShopIds);
        $this->assertNotContains($inactiveShop->id, $activeShopIds);

        $directory = $this->getJson('/api/public/shops?search='.$activeShop->name.'&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $activeShop->id)
            ->assertJsonPath('meta.per_page', 1);
        $this->assertSame(1, $directory->json('meta.total'));
        $this->assertNotContains($inactiveShop->id, collect($directory->json('data'))->pluck('id')->all());
        $this->getJson('/api/public/shops/'.$inactiveShop->slug)->assertNotFound();

        $products = $this->getJson('/api/public/shops/'.$activeShop->slug.'/products?per_page=100')
            ->assertOk();
        $listedIds = collect($products->json('data'))->pluck('product_id')->all();
        $this->assertSame([$assigned->id], $listedIds);

        $this->getJson('/api/public/shops/'.$activeShop->slug.'/products/'.$assigned->id)
            ->assertOk()
            ->assertJsonPath('data.id', $activeListing->id)
            ->assertJsonPath('data.product_id', $assigned->id)
            ->assertJsonPath('data.price', '18.50')
            ->assertJsonPath('data.name', 'Assigned Global Product')
            ->assertJsonPath('data.description', 'Global description')
            ->assertJsonPath('data.thumbnail', 'products/thumbnails/global.jpg');

        $this->getJson('/api/public/shops/'.$activeShop->slug.'/products/'.$unassignedProduct->id)->assertNotFound();
        $this->getJson('/api/public/shops/'.$activeShop->slug.'/products/'.$inactiveListingProduct->id)->assertNotFound();
        $this->getJson('/api/public/shops/'.$activeShop->slug.'/products/'.$otherShopProduct->id)->assertNotFound();
    }

    public function test_public_storefront_returns_shop_specific_overrides_and_searches_global_fallback_fields(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create(['status' => 'active']);
        $product = Product::factory()->for($owner, 'creator')->create([
            'name' => 'Global Linen Shirt',
            'description' => 'Natural linen fabric',
            'thumbnail' => 'products/global-shirt.jpg',
        ]);
        $listing = ShopProduct::create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'name' => 'Harbor Exclusive Shirt',
            'description' => 'Limited shop colorway',
            'price' => '34.95',
            'thumbnail' => 'shop-products/harbor-shirt.jpg',
            'status' => 'active',
        ]);

        $this->getJson('/api/public/shops/'.$shop->slug.'/products?q=Natural%20linen')
            ->assertOk()
            ->assertJsonPath('data.0.id', $listing->id)
            ->assertJsonPath('data.0.name', 'Harbor Exclusive Shirt')
            ->assertJsonPath('data.0.description', 'Limited shop colorway')
            ->assertJsonPath('data.0.price', '34.95')
            ->assertJsonPath('data.0.thumbnail', 'shop-products/harbor-shirt.jpg');

        $this->getJson('/api/public/shops/'.$shop->slug.'/products?q=Harbor%20Exclusive')
            ->assertOk()->assertJsonPath('data.0.product_id', $product->id);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Global Linen Shirt',
            'description' => 'Natural linen fabric',
            'thumbnail' => 'products/global-shirt.jpg',
        ]);
    }

    public function test_public_product_sorting_uses_shop_listing_price_and_pagination(): void
    {
        $owner = User::factory()->shopOwner()->create();
        $shop = Shop::factory()->for($owner, 'owner')->create(['status' => 'active']);
        $products = collect([
            Product::factory()->for($owner, 'creator')->create(['name' => 'Price Item One']),
            Product::factory()->for($owner, 'creator')->create(['name' => 'Price Item Two']),
            Product::factory()->for($owner, 'creator')->create(['name' => 'Price Item Three']),
        ]);

        foreach (['31.00', '12.00', '22.00'] as $index => $price) {
            ShopProduct::create([
                'shop_id' => $shop->id,
                'product_id' => $products[$index]->id,
                'price' => $price,
                'status' => 'active',
            ]);
        }

        $ascending = $this->getJson('/api/public/shops/'.$shop->slug.'/products?sort=price_asc&per_page=2')
            ->assertOk()
            ->assertJsonPath('data.0.price', '12.00')
            ->assertJsonPath('data.1.price', '22.00')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2);

        $this->assertSame(2, count($ascending->json('data')));
        $this->getJson('/api/public/shops/'.$shop->slug.'/products?sort=price_desc&per_page=1')
            ->assertOk()->assertJsonPath('data.0.price', '31.00');
    }
}
