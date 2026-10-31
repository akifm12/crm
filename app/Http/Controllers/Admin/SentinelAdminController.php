<?php
// app/Http/Controllers/Admin/SentinelAdminController.php
// Tools → Sentinel: one place to run Sentinel AML (aml.bluearrow.ae): sign-ups, clients and their plans,
// users and roles, API keys, orders and the activity log. Sentinel does the work (services/SentinelAdmin.php).

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SentinelAdmin;
use Illuminate\Http\Request;

class SentinelAdminController extends Controller
{
    private const TABS = ['overview', 'signups', 'clients', 'users', 'orders', 'requests', 'log'];
    private const PLANS = ['TRIAL' => 'Trial', 'ESSENTIAL' => 'Essential', 'COMPLETE' => 'Complete',
                           'API' => 'Corporate API', 'PREMIUM' => 'Premium data'];
    private const ROLES = ['analyst' => 'Analyst', 'mlro' => 'MLRO', 'admin' => 'Admin (Blue Arrow only)'];

    public function __construct(private SentinelAdmin $sentinel) {}

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'overview';
        $q   = trim((string) $request->query('q', ''));

        $data = match ($tab) {
            'overview' => $this->sentinel->get('/overview'),
            'signups'  => $this->sentinel->get('/users', ['trial' => '1', 'q' => $q]),
            'clients'  => $this->sentinel->get('/companies', array_filter([
                              'q' => $q, 'plan' => $request->query('plan'), 'portal' => $request->query('portal') ? '1' : null])),
            'users'    => $this->sentinel->get('/users', ['q' => $q]),
            'orders'   => $this->sentinel->orders(),
            'requests' => $this->sentinel->get('/key-requests'),
            'log'      => $this->sentinel->get('/log'),
        };
        $clients = in_array($tab, ['users', 'signups'], true) ? ($this->sentinel->get('/companies') ?? []) : [];
        $media   = $tab === 'overview' ? $this->sentinel->mediaUsage() : null;
        $tm      = $tab === 'overview' ? $this->sentinel->get('/transaction-monitoring') : null;

        return view('admin.sentinel.index', [
            'tab' => $tab, 'q' => $q, 'data' => $data, 'clients' => $clients, 'media' => $media, 'tm' => $tm,
            'plans' => self::PLANS, 'roles' => self::ROLES, 'configured' => $this->sentinel->configured(),
        ]);
    }

    public function company(int $id)
    {
        $data = $this->sentinel->get("/companies/{$id}");
        abort_if($data === null, 404, 'Client not found in Sentinel');
        return view('admin.sentinel.company', [
            'd' => $data, 'plans' => self::PLANS, 'roles' => self::ROLES,
            'clients' => $this->sentinel->get('/companies') ?? [],
            'tm' => $this->sentinel->get("/transaction-monitoring/companies/{$id}"),
        ]);
    }

    // ── Clients ───────────────────────────────────────────────────────────

    public function storeCompany(Request $request)
    {
        $v = $request->validate(['name' => 'required|string|max:200', 'plan' => 'required|string']);
        $r = $this->sentinel->send('POST', '/companies', $v);
        return $r['ok']
            ? redirect()->route('sentinel-admin.company', $r['data']['id'])->with('success', "Client “{$v['name']}” created.")
            : back()->with('error', $r['error'])->withInput();
    }

    public function updateCompany(Request $request, int $id)
    {
        $v = $request->validate([
            'name' => 'required|string|max:200', 'quota' => 'nullable|integer|min:0',
            'data_tier' => 'required|in:standard,premium',
        ]);
        return $this->done($this->sentinel->send('PATCH', "/companies/{$id}", [
            'name' => $v['name'], 'quota' => $v['quota'], 'dataTier' => $v['data_tier'],
            'idScanEnabled' => $request->boolean('id_scan_enabled'),
        ]), 'Client details saved.');
    }

    public function changePlan(Request $request, int $id)
    {
        $v = $request->validate(['plan' => 'required|string', 'expiry_date' => 'nullable|date_format:Y-m-d']);
        return $this->done($this->sentinel->send('POST', "/companies/{$id}/plan",
            ['plan' => $v['plan'], 'expiryDate' => $v['expiry_date'] ?? null]),
            'Plan changed to ' . (self::PLANS[$v['plan']] ?? $v['plan']) . '. The team has been emailed.');
    }

    // The Transaction Monitoring add-on: on or off (records are always kept)
    public function transactionMonitoring(Request $request, int $id)
    {
        $on = $request->boolean('enabled');
        return $this->done($this->sentinel->send('PUT', "/transaction-monitoring/companies/{$id}", ['enabled' => $on]),
            $on ? 'Transaction monitoring switched on. The client sees a Transactions tab in Sentinel.' : 'Transaction monitoring switched off. Existing records are kept.');
    }

    public function destroyCompany(int $id)
    {
        $r = $this->sentinel->send('DELETE', "/companies/{$id}");
        return $r['ok']
            ? redirect()->route('sentinel-admin.index', ['tab' => 'clients'])->with('success', 'Client deleted.')
            : back()->with('error', $r['error']);
    }

    // ── API keys ──────────────────────────────────────────────────────────

    public function storeKey(Request $request, int $id)
    {
        $v = $request->validate(['mode' => 'required|in:live,test', 'label' => 'nullable|string|max:200']);
        $r = $this->sentinel->send('POST', "/companies/{$id}/keys", $v);
        if (! $r['ok']) {
            return back()->with('error', $r['error']);
        }
        // Shown once on the next page; Sentinel only keeps its hash
        return back()->with('new_key', $r['data'])->with('success', ucfirst($v['mode']) . ' API key created. Copy it now: it will not be shown again.');
    }

    public function revokeKey(int $id, string $keyId)
    {
        return $this->done($this->sentinel->send('DELETE', "/companies/{$id}/keys/" . rawurlencode($keyId)), 'API key revoked.');
    }

    // ── Users ─────────────────────────────────────────────────────────────

    public function storeUser(Request $request)
    {
        $v = $request->validate([
            'email' => 'required|email|max:255', 'full_name' => 'required|string|max:200',
            'company_id' => 'required|integer', 'role' => 'required|in:analyst,mlro,admin',
        ]);
        return $this->done($this->sentinel->send('POST', '/users', [
            'email' => $v['email'], 'fullName' => $v['full_name'], 'companyId' => $v['company_id'], 'role' => $v['role'],
        ]), "User {$v['email']} created. They have been emailed a link to set their password.");
    }

    public function updateUser(Request $request, int $userId)
    {
        $v = $request->validate([
            'full_name' => 'nullable|string|max:200', 'role' => 'nullable|in:analyst,mlro,admin',
            'company_id' => 'nullable|integer', 'quota' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date_format:Y-m-d', 'is_active' => 'nullable|in:0,1',
            'monitoring_access' => 'nullable|in:0,1',
        ]);
        $payload = array_filter([
            'fullName' => $v['full_name'] ?? null, 'role' => $v['role'] ?? null, 'companyId' => $v['company_id'] ?? null,
            'quota' => $v['quota'] ?? null, 'expiryDate' => $v['expiry_date'] ?? null,
        ], fn ($x) => $x !== null && $x !== '');
        foreach (['is_active' => 'isActive', 'monitoring_access' => 'monitoringAccess'] as $field => $key) {
            if (isset($v[$field])) {
                $payload[$key] = $v[$field] === '1';
            }
        }
        $message = match (true) {
            ($payload['isActive'] ?? null) === false => 'User deactivated: they can no longer sign in.',
            ($payload['isActive'] ?? null) === true  => 'User reactivated.',
            default                                   => 'User saved.',
        };
        return $this->done($this->sentinel->send('PATCH', "/users/{$userId}", $payload), $message);
    }

    public function passwordLink(int $userId)
    {
        return $this->done($this->sentinel->send('POST', "/users/{$userId}/password-link"), 'Password reset link emailed to the user.');
    }

    public function convertUser(Request $request, int $userId)
    {
        $v = $request->validate([
            'company_name' => 'required|string|max:200', 'plan' => 'required|string',
            'expiry_date' => 'nullable|date_format:Y-m-d',
        ]);
        $r = $this->sentinel->send('POST', "/users/{$userId}/convert", [
            'companyName' => $v['company_name'], 'plan' => $v['plan'], 'expiryDate' => $v['expiry_date'] ?? null,
        ]);
        return $r['ok']
            ? redirect()->route('sentinel-admin.company', $r['data']['companyId'])
                ->with('success', "“{$v['company_name']}” is now a client on " . (self::PLANS[$v['plan']] ?? $v['plan']) . '.')
            : back()->with('error', $r['error'])->withInput();
    }

    public function destroyUser(int $userId)
    {
        return $this->done($this->sentinel->send('DELETE', "/users/{$userId}"), 'User deleted.');
    }

    // ── Orders and API key requests ───────────────────────────────────────

    public function orderPaid(Request $request, int $orderId)
    {
        $v = $request->validate(['invoice_ref' => 'nullable|string|max:100']);
        return $this->done($this->sentinel->send('POST', "/admin/orders/{$orderId}/paid",
            ['invoiceRef' => $v['invoice_ref'] ?? null], '/billing'),
            'Order marked paid: the plan is active and the client has been emailed.');
    }

    public function orderCancel(int $orderId)
    {
        return $this->done($this->sentinel->send('POST', "/admin/orders/{$orderId}/cancel", [], '/billing'), 'Order cancelled.');
    }

    public function keyRequestStatus(Request $request, int $requestId)
    {
        $v = $request->validate(['status' => 'required|in:PENDING,CONTACTED,ISSUED,DECLINED']);
        return $this->done($this->sentinel->send('PATCH', "/key-requests/{$requestId}", $v), 'Request updated.');
    }

    private function done(array $r, string $success)
    {
        return $r['ok'] ? back()->with('success', $success) : back()->with('error', $r['error'])->withInput();
    }
}
