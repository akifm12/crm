<?php
// app/Http/Controllers/GuideController.php
// Public Compliance Guide — content lives in App\Support\ComplianceGuide.

namespace App\Http\Controllers;

use App\Support\ComplianceGuide;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function index(): View
    {
        return view('public.guide.index', [
            'topics'  => array_map(fn ($slug) => ComplianceGuide::topic($slug), array_keys(ComplianceGuide::topics())),
            'sectors' => ComplianceGuide::SECTORS,
        ]);
    }

    public function show(string $topic): View
    {
        $t = ComplianceGuide::topic($topic) ?? abort(404);
        [$prev, $next] = ComplianceGuide::neighbours($topic);

        return view('public.guide.show', [
            't'       => $t,
            'prev'    => $prev,
            'next'    => $next,
            'all'     => ComplianceGuide::topics(),
            'sectors' => ComplianceGuide::SECTORS,
        ]);
    }
}
