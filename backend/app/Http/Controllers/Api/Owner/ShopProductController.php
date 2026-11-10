<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerListRequest;
use App\Http\Requests\Owner\StoreShopProductRequest;
use App\Http\Requests\Owner\UpdateShopProductRequest;
use App\Http\Resources\Owner\ShopProductResource;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Services\PublicImageStorage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ShopProductController extends Controller
{
    public function __construct(private readonly PublicImageStorage $images) {}

    public function index(OwnerListRequest $request, int $shopId)
    {
        $shop = $this->ownedShop($request, $shopId);
        $filters = $request->validated();
        $listings = ShopProduct::query()->where('shop_id', $shopId)
            ->whereHas('shop', fn ($query) => $query->where('owner_id', $request->user()->id))
            ->with(['shop', 'product.creator'])
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhereHas('product', fn ($product) => $product->where('name', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ShopProductResource::collection($listings);
    }

    public function store(StoreShopProductRequest $request, int $shopId): JsonResponse
    {
        $shop = $this->ownedShop($request, $shopId);
        $data = $request->validated();
        $imagePath = $request->hasFile('thumbnail')
            ? $this->images->store($request->file('thumbnail'), 'shop-products/thumbnails')
            : null;

        try {
            $duplicate = false;
            $listing = DB::transaction(function () use ($request, $shopId, $data, $imagePath, &$duplicate): ?ShopProduct {
                $shop = Shop::query()->where('owner_id', $request->user()->id)->lockForUpdate()->findOrFail($shopId);
                Product::query()->lockForUpdate()->findOrFail($data['product_id']);

                if ($shop->listings()->where('product_id', $data['product_id'])->exists()) {
                    $duplicate = true;

                    return null;
                }

                return $shop->listings()->create([
                    ...$data,
                    'thumbnail' => $imagePath,
                    'status' => 'active',
                ]);
            });
        } catch (UniqueConstraintViolationException $exception) {
            $this->images->delete($imagePath, 'shop-products/thumbnails');

            return $this->duplicateListingResponse();
        } catch (Throwable $exception) {
            $this->images->delete($imagePath, 'shop-products/thumbnails');
            throw $exception;
        }

        if ($duplicate) {
            $this->images->delete($imagePath, 'shop-products/thumbnails');

            return $this->duplicateListingResponse();
        }

        return response()->json([
            'message' => 'Product added to shop successfully.',
            'data' => ShopProductResource::make($listing->load(['shop', 'product.creator']))->resolve($request),
        ], 201);
    }

    public function update(UpdateShopProductRequest $request, int $shopId, int $listingId): JsonResponse
    {
        $shop = $this->ownedShop($request, $shopId);
        $listing = $shop->listings()->with('product')->findOrFail($listingId);
        $data = $request->validated();
        $oldImagePath = $listing->getRawOriginal('thumbnail');
        $newImagePath = $oldImagePath;
        $removeImage = (bool) ($data['remove_thumbnail'] ?? false);
        $thumbnailWasSent = array_key_exists('thumbnail', $data);
        unset($data['remove_thumbnail'], $data['thumbnail']);

        if ($request->hasFile('thumbnail')) {
            $newImagePath = $this->images->store($request->file('thumbnail'), 'shop-products/thumbnails');
            $data['thumbnail'] = $newImagePath;
        } elseif ($removeImage || $thumbnailWasSent) {
            $newImagePath = null;
            $data['thumbnail'] = null;
        }

        try {
            $listing = DB::transaction(function () use ($request, $shopId, $listingId, $data): ShopProduct {
                $shop = Shop::query()->where('owner_id', $request->user()->id)->lockForUpdate()->findOrFail($shopId);
                $listing = $shop->listings()->lockForUpdate()->findOrFail($listingId);
                $listing->update($data);

                return $listing;
            });
        } catch (Throwable $exception) {
            if ($newImagePath !== $oldImagePath) {
                $this->images->delete($newImagePath, 'shop-products/thumbnails');
            }
            throw $exception;
        }

        if ($newImagePath !== $oldImagePath) {
            $this->images->delete($oldImagePath, 'shop-products/thumbnails');
        }

        return response()->json([
            'message' => 'Shop listing updated successfully.',
            'data' => ShopProductResource::make($listing->fresh()->load(['shop', 'product.creator']))->resolve($request),
        ]);
    }

    public function destroy(Request $request, int $shopId, int $listingId): JsonResponse
    {
        $imagePath = DB::transaction(function () use ($request, $shopId, $listingId): ?string {
            $shop = Shop::query()->where('owner_id', $request->user()->id)->lockForUpdate()->findOrFail($shopId);
            $listing = $shop->listings()->lockForUpdate()->findOrFail($listingId);
            $imagePath = $listing->getRawOriginal('thumbnail');
            $listing->delete();

            return $imagePath;
        });

        $this->images->delete($imagePath, 'shop-products/thumbnails');

        return response()->json(null, 204);
    }

    private function ownedShop(Request $request, int $shopId): Shop
    {
        return Shop::query()->where('owner_id', $request->user()->id)->findOrFail($shopId);
    }

    private function duplicateListingResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'This global product is already listed in this shop.',
            'code' => 'DUPLICATE_SHOP_LISTING',
        ], 409);
    }
}
