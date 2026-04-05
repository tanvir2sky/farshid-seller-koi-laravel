@php
    use Botble\Marketplace\Supports\VendorImpersonation;
@endphp
@if (VendorImpersonation::isActive())
    @php($payload = VendorImpersonation::payload())
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <span class="mb-0">
            {{ trans('plugins/marketplace::marketplace.impersonation.banner', ['store' => $payload['store_name'] ?? '']) }}
        </span>
        <form
            action="{{ route('marketplace.vendor.admin-impersonation.stop') }}"
            method="POST"
            class="mb-0"
        >
            @csrf
            <x-core::button
                type="submit"
                size="sm"
                color="dark"
            >
                {{ trans('plugins/marketplace::marketplace.impersonation.return_to_admin') }}
            </x-core::button>
        </form>
    </div>
@endif
