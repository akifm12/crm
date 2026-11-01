<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AnyAccountingAccess
{
    private const ACCOUNTING_MODULES = [
        'bullion_accounting',
        'general_accounting',
        'real_estate_accounting',
    ];

    public function handle(Request $request, Closure $next)
    {
        $tenant  = app('tenant');
        $modules = $tenant->settings['modules'] ?? [];

        foreach (self::ACCOUNTING_MODULES as $module) {
            if (in_array($module, $modules, true)) {
                return $next($request);
            }
        }

        // Admin users always get access
        $user = auth()->user();
        if ($user && $user->tenant_id === null) {
            return $next($request);
        }

        abort(403, 'No accounting module is enabled for your account.');
    }
}
