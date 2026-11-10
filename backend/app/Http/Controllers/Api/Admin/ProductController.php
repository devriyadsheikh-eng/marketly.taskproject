<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Product;
use App\Services\PublicImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    public function __construct(private readonly PublicImageStorage $images) {}

    public function index(AdminListRequest $request)
    {
        $filters = $request->validated();
        $products = Product::query()->with('creator')->withCount('listings')
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(fn ($query) => $query->where('name', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%'));
            })
            ->when($filters['owner_id'] ?? null, fn ($query, $ownerId) => $query->where('created_by', $ownerId))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $imagePath = $request->hasFile('thumbnail') ? $this->images->store($request->file('thumbnail'), 'products/thumbnails') : null;
        unset($data['thumbnail']);

        try {
            $product = DB::transaction(fn () => Product::create([
                ...$data,
                'created_by' => $request->user()->id,
                'thumbnail' => $imagePath,
            ]));
        } catch (Throwable $exception) {
            $this->images->delete($imagePath, 'products/thumbnails');
            throw $exception;
        }

        return response()->json([
            'message' => 'Global product created successfully.',
            'data' => ProductResource::make($product->load('creator')->loadCount('listings'))->resolve($request),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with('creator')->withCount('listings')->findOrFail($id);

        return response()->json(['data' => ProductResource::make($product)->resolve(request())]);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        $oldImagePath = $product->thumbnail;
        $newImagePath = $oldImagePath;
        $removeImage = (bool) ($data['remove_thumbnail'] ?? false);
        unset($data['thumbnail'], $data['remove_thumbnail']);

        if ($request->hasFile('thumbnail')) {
            $newImagePath = $this->images->store($request->file('thumbnail'), 'products/thumbnails');
        } elseif ($removeImage) {
            $newImagePath = null;
        }

        try {
            DB::transaction(fn () => $product->update([...$data, 'thumbnail' => $newImagePath]));
        } catch (Throwable $exception) {
            if ($newImagePath !== $oldImagePath) {
                $this->images->delete($newImagePath, 'products/thumbnails');
            }
            throw $exception;
        }

        if ($newImagePath !== $oldImagePath) {
            $this->images->delete($oldImagePath, 'products/thumbnails');
        }

        return response()->json([
            'message' => 'Global product updated successfully.',
            'data' => ProductResource::make($product->fresh()->load('creator')->loadCount('listings'))->resolve($request),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $listingCount = 0;
        $imagePath = null;

        DB::transaction(function () use ($id, &$listingCount, &$imagePath): void {
            $product = Product::query()->lockForUpdate()->findOrFail($id);
            $listingCount = $product->listings()->count();

            if ($listingCount > 0) {
                return;
            }

            $imagePath = $product->thumbnail;
            $product->delete();
        });

        if ($listingCount > 0) {
            return response()->json([
                'message' => 'This product is assigned to shops and cannot be deleted while listings exist.',
                'code' => 'PRODUCT_HAS_LISTINGS',
                'listing_count' => $listingCount,
            ], 409);
        }

        $this->images->delete($imagePath, 'products/thumbnails');

        return response()->json(null, 204);
    }
}
