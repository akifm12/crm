<?php
// app/Http/Controllers/Admin/WhatsAppController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WhatsAppController extends Controller
{
    private string $baseUrl;
    private string $user;
    private string $password;
    private string $sessionCacheKey = 'wa_manager_session_cookie';

    public function __construct()
    {
        $this->baseUrl  = config('whatsapp.manager_url', 'http://127.0.0.1:3001');
        $this->user     = config('whatsapp.manager_user', 'akif');
        $this->password = config('whatsapp.manager_password', '');
    }

    // ── Main view ──────────────────────────────────────────────────────────

    public function index()
    {
        return view('admin.whatsapp.index');
    }

    // ── Get or create session with WA manager ──────────────────────────────

    private function getSessionCookie(): ?string
    {
        // Return cached session if we have one
        if (Cache::has($this->sessionCacheKey)) {
            return Cache::get($this->sessionCacheKey);
        }

        // Authenticate with WA manager
        $response = Http::timeout(10)
            ->withOptions(['verify' => false])
            ->post("{$this->baseUrl}/api/login", [
                'username' => $this->user,
                'password' => $this->password,
            ]);

        if (!$response->successful()) {
            return null;
        }

        // Extract session cookie
        $cookies = $response->cookies();
        $sessionCookie = null;

        foreach ($cookies as $cookie) {
            if (str_contains($cookie->getName(), 'session') || str_contains($cookie->getName(), 'connect')) {
                $sessionCookie = $cookie->getName() . '=' . $cookie->getValue();
                break;
            }
        }

        // Also check Set-Cookie header directly
        if (!$sessionCookie) {
            $setCookie = $response->header('Set-Cookie');
            if ($setCookie) {
                $sessionCookie = explode(';', $setCookie)[0];
            }
        }

        if ($sessionCookie) {
            Cache::put($this->sessionCacheKey, $sessionCookie, now()->addHours(23));
        }

        return $sessionCookie;
    }

    private function waHttp()
    {
        $cookie = $this->getSessionCookie();
        return Http::timeout(30)
            ->withOptions(['verify' => false])
            ->withHeaders(array_filter([
                'Content-Type' => 'application/json',
                'Cookie'       => $cookie,
            ]));
    }

    private function retryWithFreshSession(callable $request)
    {
        try {
            $response = $request($this->waHttp());

            // If unauthorized, clear session and retry once
            if ($response->status() === 401 || $response->status() === 302) {
                Cache::forget($this->sessionCacheKey);
                $response = $request($this->waHttp());
            }

            return response($response->body(), $response->successful() ? 200 : $response->status())
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Proxy endpoints ────────────────────────────────────────────────────

    public function status()
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->get("{$this->baseUrl}/api/status")
        );
    }

    public function groups()
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->get("{$this->baseUrl}/api/groups")
        );
    }

    public function refreshGroups()
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->post("{$this->baseUrl}/api/groups/refresh")
        );
    }

    public function schedules()
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->get("{$this->baseUrl}/api/schedules")
        );
    }

    public function addSchedule(Request $request)
    {
        $data = $request->all();
        // groupIds must be JSON string for multer-based WA manager
        if (isset($data['groupIds']) && is_array($data['groupIds'])) {
            $data['groupIds'] = json_encode($data['groupIds']);
        }
        return $this->retryWithFreshSession(fn($http) =>
            $http->asForm()->post("{$this->baseUrl}/api/schedules", $data)
        );
    }

    public function updateSchedule(Request $request, string $id)
    {
        $data = $request->all();
        if (isset($data['groupIds']) && is_array($data['groupIds'])) {
            $data['groupIds'] = json_encode($data['groupIds']);
        }
        return $this->retryWithFreshSession(fn($http) =>
            $http->asForm()->put("{$this->baseUrl}/api/schedules/{$id}", $data)
        );
    }

    public function deleteSchedule(string $id)
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->delete("{$this->baseUrl}/api/schedules/{$id}")
        );
    }

    public function sendImmediate(Request $request)
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->post("{$this->baseUrl}/api/send/immediate", $request->all())
        );
    }

    public function reconnect()
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->post("{$this->baseUrl}/api/whatsapp/reconnect")
        );
    }

    public function disconnect()
    {
        Cache::forget($this->sessionCacheKey);
        return $this->retryWithFreshSession(fn($http) =>
            $http->post("{$this->baseUrl}/api/whatsapp/disconnect")
        );
    }

    public function logs()
    {
        return $this->retryWithFreshSession(fn($http) =>
            $http->get("{$this->baseUrl}/api/logs")
        );
    }
}