<?php

namespace Botble\PopupAds\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PopupAdRequest extends Request
{
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'status'           => [Rule::in(BaseStatusEnum::values())],
            'image'            => ['nullable', 'string', 'max:255'],
            'title'            => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'url'              => ['nullable', 'url', 'max:255'],
            'open_in_new_tab'  => ['nullable', 'boolean'],
            'delay_seconds'    => ['required', 'integer', 'min:0', 'max:3600'],
            'dismiss_duration' => ['required', Rule::in(['session', '1_day', '7_days', '30_days', 'forever'])],
            'started_at'       => ['nullable', 'date'],
            'ended_at'         => ['nullable', 'date', 'after_or_equal:started_at'],
            'order'            => ['required', 'integer', 'min:0', 'max:127'],
        ];
    }
}
