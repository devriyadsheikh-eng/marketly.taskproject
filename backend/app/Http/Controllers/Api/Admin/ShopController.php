<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\DeleteShopRequest;
use App\Http\Requests\Admin\StoreShopRequest;
use App\Http\Requests\Admin\UpdateShopRequest;
use App\Http\Resources\Admin\ShopResource;
use App\Models\Shop;
use App\Services\PublicImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ShopController extends Controller
{
    public function __construct(private readonly PublicImageStorage $images) {}

    public function index(AdminListRequest $request)
    {
        $filters = $request->validated();
        $shops = Shop::query()->with('owner')->withCount('listings')
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('slug', 'like', '%'.$term.'%')
                        ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%'));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['owner_id'] ?? null, fn ($query, $ownerId) => $query->where('owner_id', $ownerId))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ShopResource::collection($shops);
    }

    public function store(StoreShopRequest $request): JsonResponse
    {
        $data = $request->validated();
        $imagePath = $request->hasFile('logo') ? $this->images->store($request->file('logo'), 'shops/logos') : null;
        unset($data['logo']);

        try {
            $shop = DB::transaction(fn () => Shop::create([...$data, 'logo' => $imagePath]));
        } catch (Throwable $exception) {
            $this->images->delete($imagePath, 'shops/logos');
            throw $exception;
        }

        return response()->json([
            'message' => 'Shop created successfully.',
            'data' => ShopResource::make($shop->load('owner'))->resolve($request),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $shop = Shop::with('owner')->withCount('listings')->findOrFail($id);

        return response()->json(['data' => ShopResource::make($shop)->resolve(request())]);
    }

    public function update(UpdateShopRequest $request, int $id): JsonResponse
    {
        $shop = Shop::findOrFail($id);
        $data = $request->validated();
        $oldImagePath = $shop->logo;
        $newImagePath = $oldImagePath;
        $removeImage = (bool) ($data['remove_logo'] ?? false);
        unset($data['logo'], $data['remove_logo']);

        if ($request->hasFile('logo')) {
            $newImagePath = $this->images->store($request->file('logo'), 'shops/logos');
        } elseif ($removeImage) {
            $newImagePath = null;
        }

        try {
            DB::transaction(fn () => $shop->update([...$data, 'logo' => $newImagePath]));
        } catch (Throwable $exception) {
            if ($newImagePath !== $oldImagePath) {
                $this->images->delete($newImagePath, 'shops/logos');
            }
            throw $exception;
        }

        if ($newImagePath !== $oldImagePath) {
            $this->images->delete($oldImagePath, 'shops/logos');
        }

        return response()->json([
            'message' => 'Shop updated successfully.',
            'data' => ShopResource::make($shop->fresh()->load('owner')->loadCount('listings'))->resolve($request),
        ]);
    }

    public function destroy(DeleteShopRequest $request, int $id): JsonResponse
    {
        $deleteListings = $request->boolean('delete_listings');
        $imagePath = null;
        $listingImagePaths = [];
        $listingCount = 0;

        DB::transaction(function () use ($id, $deleteListings, &$imagePath, &$listingImagePaths, &$listingCount): void {
            $shop = Shop::query()->lockForUpdate()->findOrFail($id);
            $listingCount = $shop->listings()->count();
            $imagePath = $shop->logo;

            if ($listingCount > 0 && ! $deleteListings) {
                return;
            }

            if ($deleteListings) {
                $listingImagePaths = $shop->listings()->whereNotNull('thumbnail')->pluck('thumbnail')->all();
                $shop->listings()->delete();
            }

            $shop->delete();
        });

        if ($listingCount > 0 && ! $deleteListings) {
            return response()->json([
                'message' => 'This shop has product listings. Confirm delete_listings=true to remove the shop and its listings.',
                'code' => 'SHOP_HAS_LISTINGS',
                'listing_count' => $listingCount,
            ], 409);
        }

        $this->images->delete($imagePath, 'shops/logos');
        foreach ($listingImagePaths as $listingImagePath) {
            $this->images->delete($listingImagePath, 'shop-products/thumbnails');
        }

        return response()->json(null, 204);
    }
}
