<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'slug' => $this->product?->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'thumbnail' => $this->thumbnail,
            'thumbnail_url' => $this->thumbnail ? $request->getSchemeAndHttpHost().'/storage/'.$this->thumbnail : null,
        ];
    }
}
