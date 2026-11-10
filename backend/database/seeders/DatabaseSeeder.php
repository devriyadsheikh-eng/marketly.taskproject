<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'System Admin', 'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
        $owner1 = User::updateOrCreate(['email' => 'owner1@example.com'], [
            'name' => 'Amina Rahman', 'password' => Hash::make('password'),
            'role' => User::ROLE_SHOP_OWNER, 'is_active' => true,
        ]);
        $owner2 = User::updateOrCreate(['email' => 'owner2@example.com'], [
            'name' => 'Karim Ahmed', 'password' => Hash::make('password'),
            'role' => User::ROLE_SHOP_OWNER, 'is_active' => true,
        ]);

        $shops = collect([
            [$owner1, 'Amina Home & Living'], [$owner1, 'Amina Tech Corner'], [$owner2, 'Karim Essentials'],
        ])->map(function (array $entry) {
            [$owner, $name] = $entry;

            return Shop::updateOrCreate(['slug' => Str::slug($name)], [
                'owner_id' => $owner->id, 'name' => $name,
                'description' => "Demo storefront for {$name}.", 'status' => 'active',
            ]);
        });

        $catalog = [
            ['name' => 'Classic Cotton T-Shirt', 'description' => 'Soft everyday cotton tee.'],
            ['name' => 'Ceramic Coffee Mug', 'description' => 'Durable glazed ceramic mug.'],
            ['name' => 'Wireless Mouse', 'description' => 'Compact wireless mouse with USB receiver.'],
            ['name' => 'Canvas Tote Bag', 'description' => 'Reusable heavyweight canvas tote.'],
            ['name' => 'Steel Water Bottle', 'description' => 'Insulated stainless steel bottle.'],
            ['name' => 'Desk Lamp', 'description' => 'Adjustable LED lamp for a home office.'],
        ];

        $products = collect($catalog)->map(function (array $data) use ($admin) {
            $slug = Str::slug($data['name']);

            return Product::updateOrCreate(['slug' => $slug], [
                ...$data, 'created_by' => $admin->id, 'thumbnail' => null,
            ]);
        });

        foreach ($shops as $shopIndex => $shop) {
            foreach ($products->take(4 + $shopIndex) as $index => $product) {
                ShopProduct::updateOrCreate(
                    ['shop_id' => $shop->id, 'product_id' => $product->id],
                    [
                        'name' => $shopIndex === 0 && $index === 0 ? 'Amina Select: Cotton Tee' : null,
                        'description' => null,
                        'price' => number_format(12.50 + ($index * 4.25) + ($shopIndex * 1.5), 2, '.', ''),
                        'thumbnail' => null,
                        'status' => 'active',
                    ],
                );
            }
        }
    }
}
