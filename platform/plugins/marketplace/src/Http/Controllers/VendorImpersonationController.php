<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Supports\VendorImpersonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorImpersonationController extends BaseController
{
    public function store(Request $request, Store $store)
    {
        $admin = auth()->user();
        abort_unless(
            $admin
                && ($admin->isSuperUser() || $admin->hasPermission('marketplace.store.impersonate_vendor')),
            403
        );

        $customer = $store->customer;
        abort_unless($customer && $customer->is_vendor, 404);

        Auth::guard('customer')->login($customer, remember: false);

        VendorImpersonation::start(
            $store->getKey(),
            (string) $store->name,
            $admin->getAuthIdentifier()
        );

        $request->session()->regenerate();

        return redirect()
            ->to(route('marketplace.vendor.dashboard'))
            ->with('success_msg', trans('plugins/marketplace::marketplace.impersonation.started', ['store' => $store->name]));
    }
}
