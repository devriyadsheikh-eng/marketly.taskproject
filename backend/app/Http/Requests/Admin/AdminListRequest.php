<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'nullable', Rule::in(['active', 'inactive'])],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
            'owner_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'product_id' => ['sometimes', 'nullable', 'integer', 'exists:products,id'],
            'shop_id' => ['sometimes', 'nullable', 'integer', 'exists:shops,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $active = $this->query('is_active');

        if (is_string($active) && in_array(strtolower($active), ['true', 'false'], true)) {
            $this->merge(['is_active' => filter_var($active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
