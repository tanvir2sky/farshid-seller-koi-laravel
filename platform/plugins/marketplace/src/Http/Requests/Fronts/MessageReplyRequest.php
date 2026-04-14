<?php

namespace Botble\Marketplace\Http\Requests\Fronts;

use Botble\Support\Http\Requests\Request;

class MessageReplyRequest extends Request
{
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
        ];
    }
}
