@php
    use Botble\Marketplace\Facades\MarketplaceHelper;

    $customer = auth('customer')->user();
    $canRegisterAsVendor = MarketplaceHelper::isVendorRegistrationEnabled();
    $wantsVendorOld = (bool) old('register_as_vendor');
@endphp

<div class="feed-card mb-4" id="feed-guest-post-card">
    <h4 class="mb-2">{{ __('Post a product') }}</h4>
    <p class="feed-meta mb-3">{{ __('Share a product on the feed. It will be listed under our community store until you register as a vendor.') }}</p>

    <form
        id="feed-guest-product-form"
        action="{{ route('public.feed.guest-products.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="form-group">
            <label>{{ __('Product name') }} <span class="text-danger">*</span></label>
            <input class="form-control" name="name" required maxlength="250" value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label>{{ __('Category') }} <span class="text-danger">*</span></label>
            <select class="form-control" name="categories[]" required>
                <option value="">{{ __('Select category') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('categories.0') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>{{ __('Product image') }} <span class="text-danger">*</span></label>
            <input
                class="form-control"
                type="file"
                name="images[]"
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                required
            >
            <small class="form-text text-muted">{{ __('JPG, PNG, GIF or WebP. One image required.') }}</small>
        </div>

        <a href="#" class="text-link d-inline-block mb-3" data-toggle-guest-options>{{ __('More options') }}</a>

        <div class="feed-guest-product-options d-none">
            <div class="form-group">
                <label>{{ __('Price') }}</label>
                <input class="form-control" name="price" type="number" step="0.01" min="0" value="{{ old('price', 0) }}">
            </div>
            <div class="form-group">
                <label>{{ __('Quantity') }}</label>
                <input class="form-control" name="quantity" type="number" min="0" value="{{ old('quantity', 0) }}">
            </div>
            <div class="form-group">
                <label>{{ __('SKU (optional)') }}</label>
                <input class="form-control" name="sku" value="{{ old('sku') }}">
            </div>
            <div class="form-group">
                <label>{{ __('Short description') }}</label>
                <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
            </div>
        </div>

        @if ($canRegisterAsVendor)
            <div class="form-check mb-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="register_as_vendor"
                    id="feed-register-as-vendor"
                    value="1"
                    @checked($wantsVendorOld)
                >
                <label class="form-check-label" for="feed-register-as-vendor">
                    {{ __('I want to register as a vendor and manage my own store') }}
                </label>
            </div>

            <div
                id="feed-vendor-registration-fields"
                class="feed-vendor-registration-fields {{ $wantsVendorOld ? '' : 'd-none' }} border rounded p-3 mb-3"
            >
                <p class="feed-meta mb-3">{{ __('Enter your details to create an account and become a vendor after posting.') }}</p>
                <div class="row">
                    <div class="col-md-6 form-group mb-md-0">
                        <label>{{ __('Your name') }} <span class="text-danger">*</span></label>
                        <input
                            class="form-control feed-vendor-field"
                            name="vendor_register_name"
                            maxlength="120"
                            value="{{ old('vendor_register_name', $customer?->name) }}"
                            data-vendor-required
                        >
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label>{{ __('Your email') }} <span class="text-danger">*</span></label>
                        <input
                            class="form-control feed-vendor-field"
                            name="vendor_register_email"
                            type="email"
                            maxlength="120"
                            value="{{ old('vendor_register_email', $customer?->email) }}"
                            data-vendor-required
                        >
                        <small class="form-text text-muted">{{ __('Must be unique. You will use this email to log in.') }}</small>
                    </div>
                </div>
            </div>
        @endif

        <button type="submit" class="ps-btn">{{ __('Post product') }}</button>
    </form>

    @guest('customer')
        <p class="feed-meta mt-3 mb-0">
            {{ __('Already have an account?') }}
            <a href="{{ route('customer.login') }}">{{ __('Login') }}</a>
            {{ __('or') }}
            <a href="{{ route('customer.register') }}">{{ __('Register') }}</a>
        </p>
    @else
        @if ($canRegisterAsVendor && ! $customer?->is_vendor)
            <p class="feed-meta mt-3 mb-0">
                <a href="{{ route('marketplace.vendor.become-vendor') }}">{{ __('Become a vendor') }}</a>
                {{ __('to list products under your own store.') }}
            </p>
        @endif
    @endguest
</div>
