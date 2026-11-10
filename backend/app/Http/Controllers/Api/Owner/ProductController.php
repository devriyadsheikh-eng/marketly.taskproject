<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreGlobalProductRequest;
use App\Http\Requests\Owner\UpdateGlobalProductRequest;
use App\Http\Resources\Owner\CatalogProductResource;
use App\Models\Product;
use App\Services\PublicImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    public function __construct(private readonly PublicImageStorage $images) {}

    public function store(StoreGlobalProductRequest $request): JsonResponse
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
            'data' => CatalogProductResource::make($product->load('creator')->loadCount('listings'))->resolve($request),
        ], 201);
    }

    public function update(UpdateGlobalProductRequest $request, int $id): JsonResponse
    {
        $product = Product::query()->where('created_by', $request->user()->id)->findOrFail($id);
        $data = $request->validated();
        $oldImagePath = $product->thumbnail;
        $newImagePath = $oldImagePath;
        $removeImage = (bool) ($data['remove_thumbnail'] ?? false);
        unset($data['thumbnail'], $data['remove_thumbnail']);

        if ($request->hasFile('thumbnail')) {
            $newImagePath = $this->images->store($request->file('thumbnail'), 'products/thumbnails');
            $data['thumbnail'] = $newImagePath;
        } elseif ($removeImage || array_key_exists('thumbnail', $request->validated())) {
            $newImagePath = null;
            $data['thumbnail'] = null;
        }

        try {
            DB::transaction(fn () => $product->update($data));
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
            'data' => CatalogProductResource::make($product->fresh()->load('creator')->loadCount('listings'))->resolve($request),
        ]);
    }
}
