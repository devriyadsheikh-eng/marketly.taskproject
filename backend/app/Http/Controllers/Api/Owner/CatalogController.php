<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerListRequest;
use App\Http\Resources\Owner\CatalogProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function index(OwnerListRequest $request)
    {
        $filters = $request->validated();
        $products = Product::query()->with('creator')->withCount('listings')
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(fn ($query) => $query->where('name', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%'));
            })
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return CatalogProductResource::collection($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::query()->with('creator')->withCount('listings')->findOrFail($id);

        return response()->json(['data' => CatalogProductResource::make($product)->resolve(request())]);
    }
}
