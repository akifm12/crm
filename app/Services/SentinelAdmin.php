<?php
// app/Services/SentinelAdmin.php — Sentinel administration for Tools → Sentinel.
// Calls Sentinel's /api/portal-admin (and the order admin in /api/billing/admin) with the portal's admin
// service key, naming the signed-in portal admin in X-Portal-User for Sentinel's activity log.

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SentinelAdmin
{
    private function http()
    {
        return Http::baseUrl(config('sentinel.base_url'))
            ->withHeaders([
                'X-API-Key'     => config('sentinel.admin_key'),
                'X-Portal-User' => Auth::user()?->email ?? 'portal',
            ])
            ->acceptJson()
            ->timeout(30);
    }

    /** GET a portal-admin resource; null if Sentinel can't be reached or refuses. */
    public function get(string $path, array $query = []): ?array
    {
        $r = rescue(fn () => $this->http()->get('/portal-admin' . $path, $query), null, false);
        return $r && $r->successful() ? $r->json() : null;
    }

    /** A change: ['ok' => bool, 'data' => array, 'error' => ?string] */
    public function send(string $method, string $path, array $data = [], string $prefix = '/portal-admin'): array
    {
        $r = rescue(fn () => $this->http()->send($method, $prefix . $path, ['json' => $data]), null, false);
        if (! $r) {
            return ['ok' => false, 'data' => [], 'error' => 'Sentinel could not be reached. Try again in a minute.'];
        }
        return [
            'ok'    => $r->successful(),
            'data'  => $r->json() ?? [],
            'error' => $r->successful() ? null : ($r->json('error') ?? 'Sentinel refused the change (' . $r->status() . ').'),
        ];
    }

    public function orders(): ?array
    {
        $r = rescue(fn () => $this->http()->get('/billing/admin/orders'), null, false);
        return $r && $r->successful() ? $r->json() : null;
    }

    public function configured(): bool
    {
        return (bool) config('sentinel.admin_key');
    }
}
