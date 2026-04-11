<?php

namespace Botble\Base\Http\Middleware;

use Botble\Base\Supports\Core;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureLicenseHasBeenActivated
{
    public function __construct(private Core $core)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

}
