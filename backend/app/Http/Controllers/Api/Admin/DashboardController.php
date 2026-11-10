<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => [
            'shops' => [
                'total' => Shop::count(),
                'active' => Shop::where('status', 'active')->count(),
                'inactive' => Shop::where('status', 'inactive')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
            'shop_owners' => [
                'total' => User::where('role', User::ROLE_SHOP_OWNER)->count(),
                'active' => User::where('role', User::ROLE_SHOP_OWNER)->where('is_active', true)->count(),
                'inactive' => User::where('role', User::ROLE_SHOP_OWNER)->where('is_active', false)->count(),
            ],
            'products' => ['total' => Product::count()],
            'shop_listings' => [
                'total' => ShopProduct::count(),
                'active' => ShopProduct::where('status', 'active')->count(),
                'inactive' => ShopProduct::where('status', 'inactive')->count(),
            ],
        ]]);
    }
}
