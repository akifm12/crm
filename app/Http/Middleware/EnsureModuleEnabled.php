<?php
// app/Http/Middleware/EnsureModuleEnabled.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $tenant = App::make('tenant');

        abort_unless($tenant->hasModule($module), 404, 'Module not enabled for this portal.');

        return $next($request);
    }
}
