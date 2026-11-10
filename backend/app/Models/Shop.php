<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'slug', 'description', 'logo', 'status'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'shop_products')
            ->using(ShopProduct::class)
            ->withPivot(['id', 'name', 'description', 'price', 'thumbnail', 'status'])
            ->withTimestamps();
    }

    public function listings()
    {
        return $this->hasMany(ShopProduct::class);
    }
}
