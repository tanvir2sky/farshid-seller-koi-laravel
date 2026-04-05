<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Supports\VendorImpersonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StopVendorImpersonationController extends BaseController
{
    public function __invoke(Request $request)
    {
        abort_unless(VendorImpersonation::isActive(), 403);

        $payload = VendorImpersonation::payload();
        $storeId = $payload['store_id'] ?? null;

        VendorImpersonation::stop();
        Auth::guard('customer')->logout();

        abort_unless($storeId !== null, 404);

        return redirect()
            ->to(route('marketplace.store.edit', $storeId))
            ->with('success_msg', trans('plugins/marketplace::marketplace.impersonation.stopped'));
    }
}
