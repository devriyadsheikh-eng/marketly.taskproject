<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        $shopIds = Shop::query()->where('owner_id', $ownerId)->select('id');

        return response()->json(['data' => [
            'shops' => [
                'total' => Shop::where('owner_id', $ownerId)->count(),
                'active' => Shop::where('owner_id', $ownerId)->where('status', 'active')->count(),
                'inactive' => Shop::where('owner_id', $ownerId)->where('status', 'inactive')->count(),
            ],
            'products_created' => Product::where('created_by', $ownerId)->count(),
            'catalog_products' => Product::count(),
            'listings' => [
                'total' => ShopProduct::whereIn('shop_id', $shopIds)->count(),
                'active' => ShopProduct::whereIn('shop_id', $shopIds)->where('status', 'active')->count(),
                'inactive' => ShopProduct::whereIn('shop_id', $shopIds)->where('status', 'inactive')->count(),
            ],
        ]]);
    }
}
