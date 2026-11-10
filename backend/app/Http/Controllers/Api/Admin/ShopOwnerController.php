<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminListRequest;
use App\Http\Requests\Admin\StoreShopOwnerRequest;
use App\Http\Requests\Admin\UpdateShopOwnerRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ShopOwnerController extends Controller
{
    public function index(AdminListRequest $request)
    {
        $filters = $request->validated();
        $users = User::query()->where('role', User::ROLE_SHOP_OWNER)
            ->when($filters['q'] ?? $filters['search'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null,
                fn ($query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function store(StoreShopOwnerRequest $request): JsonResponse
    {
        $data = $request->validated();
        unset($data['role']);

        $owner = User::create([
            ...$data,
            'role' => User::ROLE_SHOP_OWNER,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Shop owner created successfully.',
            'data' => UserResource::make($owner)->resolve($request),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => UserResource::make($this->findOwner($id))->resolve(request())]);
    }

    public function update(UpdateShopOwnerRequest $request, int $id): JsonResponse
    {
        $owner = $this->findOwner($id);
        $data = $request->validated();
        unset($data['password_confirmation'], $data['role']);

        DB::transaction(function () use ($owner, $data): void {
            $owner->update($data);

            if (array_key_exists('is_active', $data) && ! filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN)) {
                $owner->tokens()->delete();
                DB::table('sessions')->where('user_id', $owner->id)->delete();
            }
        });

        return response()->json([
            'message' => 'Shop owner updated successfully.',
            'data' => UserResource::make($owner->fresh())->resolve($request),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $hasDependencies = false;

        DB::transaction(function () use ($id, &$hasDependencies): void {
            $owner = User::query()->where('role', User::ROLE_SHOP_OWNER)->lockForUpdate()->findOrFail($id);
            $hasDependencies = $owner->shops()->exists() || $owner->products()->exists();

            if ($hasDependencies) {
                return;
            }

            $owner->tokens()->delete();
            DB::table('sessions')->where('user_id', $owner->id)->delete();
            $owner->delete();
        });

        if ($hasDependencies) {
            return response()->json([
                'message' => 'This account owns shops or global products and cannot be deleted until those records are reassigned or removed.',
                'code' => 'SHOP_OWNER_HAS_DEPENDENCIES',
            ], 409);
        }

        return response()->json(null, 204);
    }

    private function findOwner(int $id): User
    {
        return User::query()->where('role', User::ROLE_SHOP_OWNER)->findOrFail($id);
    }
}
