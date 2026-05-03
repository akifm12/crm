<?php
// app/Http/Middleware/ResolveTenant.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('slug');

        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->first();

        if (! $tenant) {
            abort(404, 'Portal not found.');
        }

        // Make tenant available everywhere in this request lifecycle
        App::instance('tenant', $tenant);
        $request->attributes->set('tenant', $tenant);

        // Share with all Blade views automatically
        view()->share('tenant', $tenant);

        // Share sector config so all tenant views can use $sector
        $sector = $tenant->sectorConfig();
        App::instance('sector', $sector);
        view()->share('sector', $sector);

        return $next($request);
    }
}
