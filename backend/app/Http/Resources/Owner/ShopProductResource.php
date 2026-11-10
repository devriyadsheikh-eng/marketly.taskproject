<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'thumbnail' => $this->thumbnail,
            'thumbnail_url' => $this->thumbnail ? $request->getSchemeAndHttpHost().'/storage/'.$this->thumbnail : null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'shop' => $this->whenLoaded('shop', fn () => [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
                'slug' => $this->shop->slug,
                'status' => $this->shop->status,
            ]),
            'product' => $this->whenLoaded('product', fn () => CatalogProductResource::make($this->product)->resolve($request)),
        ];
    }
}
