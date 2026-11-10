<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['created_by', 'name', 'slug', 'description', 'thumbnail'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'shop_products')
            ->using(ShopProduct::class)
            ->withPivot(['id', 'name', 'description', 'price', 'thumbnail', 'status'])
            ->withTimestamps();
    }

    public function listings()
    {
        return $this->hasMany(ShopProduct::class);
    }
}
