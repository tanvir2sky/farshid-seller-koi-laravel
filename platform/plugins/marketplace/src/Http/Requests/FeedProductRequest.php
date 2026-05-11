<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class FeedProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'integer', 'exists:ec_product_categories,id'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:120'],
            'quantity' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
