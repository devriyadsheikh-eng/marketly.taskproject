<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\PublicListRequest;
use App\Http\Requests\Public\PublicProductListRequest;
use App\Http\Resources\Public\ShopProductResource;
use App\Http\Resources\Public\ShopResource;
use App\Http\Resources\Public\CatalogProductResource;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class ShopfrontController extends Controller
{
    public function catalogProducts()
    {
        return CatalogProductResource::collection(
            Product::query()->latest('id')->paginate(8)
        );
    }

    public function shops(PublicListRequest $request)
    {
        $filters = $request->validated();
        $shops = Shop::query()->where('status', 'active')
            ->withCount(['listings as active_products_count' => fn ($query) => $query->where('status', 'active')])
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(fn ($query) => $query->where('name', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%'));
            })
            ->when(($filters['sort'] ?? 'name') === 'newest', fn ($query) => $query->latest('id'))
            ->when(($filters['sort'] ?? 'name') === 'oldest', fn ($query) => $query->oldest('id'))
            ->when(($filters['sort'] ?? 'name') === 'name', fn ($query) => $query->orderBy('name'))
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ShopResource::collection($shops);
    }

    public function showShop(string $slug): JsonResponse
    {
        $shop = Shop::query()->where('status', 'active')
            ->withCount(['listings as active_products_count' => fn ($query) => $query->where('status', 'active')])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => ShopResource::make($shop)->resolve(request())]);
    }

    public function products(PublicProductListRequest $request, string $slug)
    {
        $shop = $this->activeShop($slug);
        $filters = $request->validated();
        $listings = $shop->listings()
            ->where('status', 'active')
            ->with('product')
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('description', 'like', '%'.$term.'%')
                        ->orWhereHas('product', fn ($product) => $product
                            ->where('name', 'like', '%'.$term.'%')
                            ->orWhere('description', 'like', '%'.$term.'%')
                            ->orWhere('slug', 'like', '%'.$term.'%'));
                });
            });

        $listings = $this->applyProductSort($listings, $filters['sort'] ?? 'newest')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ShopProductResource::collection($listings);
    }

    public function showProduct(string $slug, int $productId): JsonResponse
    {
        $shop = $this->activeShop($slug);
        $listing = $shop->listings()
            ->where('status', 'active')
            ->where('product_id', $productId)
            ->with('product')
            ->firstOrFail();

        return response()->json(['data' => ShopProductResource::make($listing)->resolve(request())]);
    }

    private function activeShop(string $slug): Shop
    {
        return Shop::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
    }

    private function applyProductSort($query, string $sort)
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'oldest' => $query->oldest('id'),
            default => $query->latest('id'),
        };
    }
}
