<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $tenant = app('tenant');
        $modules = $tenant->settings['modules'] ?? [];

        if (!in_array($module, $modules, true)) {
            abort(403, 'This module is not enabled for your account.');
        }

        return $next($request);
    }
}
