<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ShopProduct extends Pivot
{
    use HasFactory;

    protected $table = 'shop_products';

    public $incrementing = true;

    protected $fillable = ['shop_id', 'product_id', 'name', 'description', 'price', 'thumbnail', 'status'];

    protected $casts = ['price' => 'decimal:2'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getNameAttribute($value): ?string
    {
        return $value ?? $this->product?->name;
    }

    public function getDescriptionAttribute($value): ?string
    {
        return $value ?? $this->product?->description;
    }

    public function getThumbnailAttribute($value): ?string
    {
        return $value ?? $this->product?->thumbnail;
    }
}
