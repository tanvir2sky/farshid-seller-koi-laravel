<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Support\Http\Requests\Request;

class FeedCommentRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:ec_products,id'],
            'content' => ['required', 'string', 'max:1000'],
        ];
    }
}
