<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($this->route('id'))],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'thumbnail' => ['sometimes', 'nullable', 'prohibited_if:remove_thumbnail,true', 'image'],
            'remove_thumbnail' => ['sometimes', 'boolean'],
        ];
    }
}
