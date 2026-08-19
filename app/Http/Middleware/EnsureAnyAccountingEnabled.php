<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EnsureAnyAccountingEnabled
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = App::make('tenant');

        $hasAny = $tenant->hasModule('bullion_accounting')
               || $tenant->hasModule('general_accounting')
               || $tenant->hasModule('real_estate_accounting');

        abort_unless($hasAny, 404, 'No accounting module is enabled for this portal.');

        return $next($request);
    }
}
