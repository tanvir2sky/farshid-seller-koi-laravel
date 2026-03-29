<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $parentId = $this->input('parent_id');
        if ($parentId === null || $parentId === '' || $parentId === 0 || $parentId === '0') {
            $this->merge(['parent_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:100000'],
            'image' => ['nullable', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                Rule::when($this->input('parent_id'), function () {
                    return Rule::exists('mp_store_categories', 'id');
                }),
            ],
            'order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
