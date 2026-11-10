<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerListRequest;
use App\Http\Resources\Admin\ShopResource;
use App\Models\Shop;

class ShopController extends Controller
{
    public function index(OwnerListRequest $request)
    {
        $filters = $request->validated();
        $shops = Shop::query()->where('owner_id', $request->user()->id)
            ->with('owner')->withCount('listings')
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(fn ($query) => $query->where('name', 'like', '%'.$term.'%')->orWhere('slug', 'like', '%'.$term.'%'));
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ShopResource::collection($shops);
    }
}
