<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\ShopController;
use App\Http\Controllers\Api\Admin\ShopListingController;
use App\Http\Controllers\Api\Admin\ShopOwnerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Owner\CatalogController;
use App\Http\Controllers\Api\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Api\Owner\ProductController as OwnerProductController;
use App\Http\Controllers\Api\Owner\ShopController as OwnerShopController;
use App\Http\Controllers\Api\Owner\ShopProductController as OwnerShopProductController;
use App\Http\Controllers\Api\Public\ShopfrontController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

Route::prefix('public')->name('public.')->group(function (): void {
    Route::get('/products', [ShopfrontController::class, 'catalogProducts'])->name('products.index');
    Route::get('/shops', [ShopfrontController::class, 'shops'])->name('shops.index');
    Route::get('/shops/{slug}', [ShopfrontController::class, 'showShop'])->name('shops.show');
    Route::get('/shops/{slug}/products', [ShopfrontController::class, 'products'])->name('shops.products.index');
    Route::get('/shops/{slug}/products/{productId}', [ShopfrontController::class, 'showProduct'])->name('shops.products.show');
});

Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'active', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/shop-listings', [ShopListingController::class, 'index'])->name('shop-listings.index');

    Route::apiResource('shops', ShopController::class)->parameters(['shops' => 'id']);
    Route::apiResource('users', ShopOwnerController::class)->parameters(['users' => 'id']);
    Route::apiResource('products', ProductController::class)->parameters(['products' => 'id']);
});

Route::prefix('owner')->name('owner.')->middleware(['auth:sanctum', 'active', 'role:shop_owner'])->group(function (): void {
    Route::get('/dashboard', OwnerDashboardController::class)->name('dashboard');
    Route::get('/shops', [OwnerShopController::class, 'index'])->name('shops.index');
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/{id}', [CatalogController::class, 'show'])->name('catalog.show');
    Route::post('/products', [OwnerProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [OwnerProductController::class, 'update'])->name('products.update');

    Route::get('/shops/{shopId}/products', [OwnerShopProductController::class, 'index'])->name('shops.products.index');
    Route::post('/shops/{shopId}/products', [OwnerShopProductController::class, 'store'])->name('shops.products.store');
    Route::put('/shops/{shopId}/products/{listingId}', [OwnerShopProductController::class, 'update'])->name('shops.products.update');
    Route::delete('/shops/{shopId}/products/{listingId}', [OwnerShopProductController::class, 'destroy'])->name('shops.products.destroy');
});
