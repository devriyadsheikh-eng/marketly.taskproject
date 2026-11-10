<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Resources\Admin\ShopProductResource;
use App\Models\ShopProduct;

class ShopListingController extends Controller
{
    public function index(AdminListRequest $request)
    {
        $filters = $request->validated();
        $listings = ShopProduct::query()->with(['shop', 'product.creator'])
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhereHas('product', fn ($product) => $product->where('name', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%'))
                        ->orWhereHas('shop', fn ($shop) => $shop->where('name', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['shop_id'] ?? null, fn ($query, $shopId) => $query->where('shop_id', $shopId))
            ->when($filters['product_id'] ?? null, fn ($query, $productId) => $query->where('product_id', $productId))
            ->when($filters['owner_id'] ?? null, fn ($query, $ownerId) => $query->whereHas('shop', fn ($shop) => $shop->where('owner_id', $ownerId)))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ShopProductResource::collection($listings);
    }
}
