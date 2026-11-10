<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopProductFactory extends Factory
{
    protected $model = ShopProduct::class;

    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'product_id' => Product::factory(),
            'name' => null,
            'description' => null,
            'price' => fake()->randomFloat(2, 2, 500),
            'thumbnail' => null,
            'status' => 'active',
        ];
    }
}
