<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Marketplace\Enums\FeedPinTypeEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class FeedPinRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'starts_at' => $this->filled('starts_at') ? $this->input('starts_at') : null,
            'ends_at' => $this->filled('ends_at') ? $this->input('ends_at') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'pin_type' => ['required', 'string', Rule::in(array_values(FeedPinTypeEnum::toArray()))],
            'target_id' => ['required', 'integer', 'min:1'],
            'priority' => ['required', 'integer', 'min:0', 'max:9999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_enabled' => ['nullable', 'in:0,1'],
        ];
    }
}
