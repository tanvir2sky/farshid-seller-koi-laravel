<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Base\Rules\EmailRule;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class FeedGuestProductRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'register_as_vendor' => $this->boolean('register_as_vendor') ? '1' : '0',
        ]);
    }

    public function rules(): array
    {
        $maxUploadMb = max(1, (float) MarketplaceHelper::maxFilesizeUploadByVendor());
        $maxUploadKb = (int) ($maxUploadMb * 1024);

        $rules = [
            'name' => ['required', 'string', 'max:250'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'integer', 'exists:ec_product_categories,id'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:120'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'register_as_vendor' => ['nullable', 'in:0,1'],
            'images' => ['required', 'array', 'min:1', 'max:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:' . max(512, $maxUploadKb)],
        ];

        if ($this->wantsVendorRegistration()) {
            $emailRule = Rule::unique((new Customer())->getTable(), 'email');

            if ($customerId = auth('customer')->id()) {
                $emailRule = $emailRule->ignore($customerId);
            }

            $rules['vendor_register_name'] = ['required', 'string', 'min:2', 'max:120'];
            $rules['vendor_register_email'] = [
                'required',
                'email',
                'max:120',
                new EmailRule(),
                $emailRule,
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => __('Product name'),
            'images' => __('Product image'),
            'images.*' => __('Product image'),
            'vendor_register_name' => __('Your name'),
            'vendor_register_email' => __('Your email'),
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => __('Please upload a product image.'),
            'images.min' => __('Please upload a product image.'),
            'vendor_register_email.unique' => __('This email is already registered. Please log in or use a different email.'),
        ];
    }

    protected function wantsVendorRegistration(): bool
    {
        return $this->boolean('register_as_vendor')
            && MarketplaceHelper::isVendorRegistrationEnabled();
    }
}
