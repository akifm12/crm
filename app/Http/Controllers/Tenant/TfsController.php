<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TfsSubmission;
use Illuminate\Http\Request;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * One record per TFS alert: the tenant's unique survey link, plus two pieces of evidence
 * they upload themselves — a screenshot of the completed survey and a screenshot of the
 * search of their client list showing no match. The portal never answers the survey.
 */
class TfsController extends Controller
{
    private const EVIDENCE = [
        'survey' => 'survey_evidence_path',
        'search' => 'search_evidence_path',
    ];

    public function index()
    {
        $tenant      = app('tenant');
        $submissions = TfsSubmission::where('tenant_id', $tenant->id)->latest()->paginate(20);

        return view('tenant.tfs.index', compact('tenant', 'submissions'));
    }

    public function create()
    {
        $tenant = app('tenant');
        return view('tenant.tfs.create', compact('tenant'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'survey_url' => ['required', 'url', 'max:2000', 'starts_with:https://,http://'],
            'title'      => 'nullable|string|max:300',
        ]);

        $tenant = app('tenant');
        $sub = TfsSubmission::create([
            'tenant_id'  => $tenant->id,
            'created_by' => auth()->id(),
            'title'      => trim($data['title'] ?? '') ?: 'TFS survey — ' . now()->format('d M Y'),
            'survey_url' => $data['survey_url'],
            'status'     => 'draft',
        ]);

        return redirect()->route('tenant.tfs.show', [$tenant->slug, $sub->id]);
    }

    public function show(string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        return view('tenant.tfs.show', compact('tenant', 'submission'));
    }

    public function upload(Request $request, string $slug, TfsSubmission $submission, string $type)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        abort_unless(isset(self::EVIDENCE[$type]), 404);

        $request->validate(['file' => 'required|file|mimes:png,jpg,jpeg,pdf|max:8192']);

        $column = self::EVIDENCE[$type];
        $file   = $request->file('file');
        $name   = $submission->id . '-' . $type . '-' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
        $file->storeAs('tfs-evidence', $name, 'public');

        if ($submission->$column) {
            Storage::disk('public')->delete($submission->$column);
        }
        $submission->$column = 'tfs-evidence/' . $name;
        $submission->status  = ($submission->survey_evidence_path && $submission->search_evidence_path) ? 'submitted' : 'partial';
        if ($submission->status === 'submitted' && !$submission->submitted_at) {
            $submission->submitted_at = now();
        }
        $submission->save();

        return back()->with('success', 'Saved.');
    }

    public function file(string $slug, TfsSubmission $submission, string $type)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);
        abort_unless(isset(self::EVIDENCE[$type]), 404);

        $rel = $submission->{self::EVIDENCE[$type]};
        abort_if(!$rel || !Storage::disk('public')->exists($rel), 404);

        return response()->file(Storage::disk('public')->path($rel));
    }

    /**
     * Shows the survey inside the portal page. The Ministry site forbids framing
     * (X-Frame-Options: SAMEORIGIN), so the page is fetched here and served from our own
     * origin. The tenant still clicks and submits themselves; this only relays their
     * clicks and keeps the survey's cookies. Only the Ministry's host is ever fetched.
     */
    public function frame(Request $request, string $slug, TfsSubmission $submission)
    {
        $tenant = app('tenant');
        abort_if($submission->tenant_id !== $tenant->id, 404);

        $start  = $submission->survey_url ?: ($submission->tfs_urls[0]['url'] ?? null);
        $target = $request->query('u') ?: $start;
        abort_unless($target && $this->allowedHost($target), 404);

        $key = 'tfs-jar-' . $submission->id;
        $jar = new CookieJar(false, Cache::get($key, []));

        $http = Http::withOptions(['cookies' => $jar, 'allow_redirects' => ['max' => 5], 'http_errors' => false])
            ->timeout(30)
            ->withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer'         => $start,
            ]);

        if ($request->isMethod('post')) {
            $raw  = preg_replace('/(^|&)_token=[^&]*/', '', $request->getContent());
            $raw  = ltrim($raw, '&');
            $resp = $http->withBody($raw, 'application/x-www-form-urlencoded')->post($target);
        } else {
            $resp = $http->get($target);
        }

        Cache::put($key, $jar->toArray(), now()->addHours(3));

        $final = (string) ($resp->effectiveUri() ?? $target);
        $type  = $resp->header('Content-Type') ?: 'text/html; charset=utf-8';
        if (!str_contains(strtolower($type), 'html')) {
            return response($resp->body(), $resp->status())->header('Content-Type', $type);
        }

        $frameUrl = route('tenant.tfs.frame', [$tenant->slug, $submission->id]);
        $html     = $this->rewrite($resp->body(), $final, $frameUrl);

        return response($html, $resp->status())->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function allowedHost(string $url): bool
    {
        $p = parse_url($url);
        $h = strtolower($p['host'] ?? '');
        return ($p['scheme'] ?? '') === 'https' && ($h === 'uaeiec.gov.ae' || str_ends_with($h, '.uaeiec.gov.ae'));
    }

    private function absolute(string $href, string $base): string
    {
        if (preg_match('#^https?://#i', $href)) return $href;
        $b = parse_url($base);
        $origin = $b['scheme'] . '://' . $b['host'] . (isset($b['port']) ? ':' . $b['port'] : '');
        if (str_starts_with($href, '//')) return $b['scheme'] . ':' . $href;
        if ($href === '' ) return $base;
        if ($href[0] === '/') return $origin . $href;
        if ($href[0] === '?') return $origin . ($b['path'] ?? '/') . $href;
        $dir = preg_replace('#[^/]*$#', '', $b['path'] ?? '/');
        return $origin . $dir . $href;
    }

    private function rewrite(string $html, string $base, string $frameUrl): string
    {
        $token = csrf_token();

        // Every form posts back through the portal.
        $html = preg_replace_callback('/<form\b[^>]*>/i', function ($m) use ($base, $frameUrl, $token) {
            $tag    = $m[0];
            $action = preg_match('/\baction\s*=\s*(["\'])(.*?)\1/is', $tag, $a) ? html_entity_decode($a[2]) : '';
            $abs    = $this->absolute($action, $base);
            $tag    = preg_replace('/\saction\s*=\s*(["\']).*?\1/is', '', $tag);
            $tag    = preg_replace('/\starget\s*=\s*(["\']).*?\1/is', '', $tag);
            $new    = $frameUrl . '?u=' . rawurlencode($abs);
            $method = preg_match('/\bmethod\s*=\s*["\']?get/i', $tag) ? 'get' : 'post';
            $tag    = rtrim(substr($tag, 0, -1)) . ' action="' . e($new) . '">';
            if ($method === 'post') {
                $tag .= '<input type="hidden" name="_token" value="' . e($token) . '">';
            }
            return $tag;
        }, $html);

        // Resolve relative links against the Ministry's site; route XHR/fetch through us.
        $shim = '<base href="' . e($base) . '"><script>(function(){var F=' . json_encode($frameUrl) . ',B=' . json_encode($base)
            . ';function m(u){try{var a=new URL(u,B);if(/(^|\.)uaeiec\.gov\.ae$/.test(a.hostname))return F+"?u="+encodeURIComponent(a.href)}catch(e){}return u}'
            . 'var o=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(me,u){arguments[1]=m(u);return o.apply(this,arguments)};'
            . 'if(window.fetch){var f=window.fetch;window.fetch=function(u,i){return f(typeof u==="string"?m(u):u,i)}}})();</script>';
        if (preg_match('/<head\b[^>]*>/i', $html)) {
            $html = preg_replace('/(<head\b[^>]*>)/i', '$1' . $shim, $html, 1);
        } else {
            $html = $shim . $html;
        }
        return $html;
    }
}
