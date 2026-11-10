<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shopId = $this->route('id');

        return [
            'owner_id' => ['sometimes', 'required', 'integer', Rule::exists('users', 'id')->where('role', 'shop_owner')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('shops', 'slug')->ignore($shopId)],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
            'logo' => ['sometimes', 'nullable', 'prohibited_if:remove_logo,true', 'image'],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }
}
