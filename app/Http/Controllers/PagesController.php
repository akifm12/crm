<?php
// app/Http/Controllers/PagesController.php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\NewsItem;
use App\Support\SectorConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PagesController extends Controller
{
    /**
     * The 6 service tiles shown publicly: 5 sectors are backed by real
     * portal features (SectorConfig), 2 are advisory-only positioning
     * matching the existing bluearrow.ae marketing site.
     */
    public static function serviceTiles(): array
    {
        return [
            [
                'slug' => 'gold', 'icon' => '🪙', 'portal_backed' => true,
                'title' => 'Bullion & Precious Metals',
                'summary' => 'Compliance for gold, precious metals & stones dealers — KYC onboarding, goAML DPMSR/STR filing, and sanctions screening for local, import and export trade.',
            ],
            [
                'slug' => 'real_estate', 'icon' => '🏢', 'portal_backed' => true,
                'title' => 'Real Estate',
                'summary' => 'AML compliance for property brokers and agents — client due diligence, source-of-funds verification, and Ejari-linked transaction monitoring.',
            ],
            [
                'slug' => 'company_services', 'icon' => '🏛', 'portal_backed' => true,
                'title' => 'Company Service Providers',
                'summary' => 'Corporate structuring, registered agent & nominee services compliance — UBO declarations, CAHRA screening, and trade license verification.',
            ],
            [
                'slug' => 'accounting', 'icon' => '🧾', 'portal_backed' => true,
                'title' => 'Accounting & Auditing',
                'summary' => 'Compliance programs for accountants and auditors — risk assessment, client screening, and regulatory reporting support.',
            ],
            [
                'slug' => 'capital_markets', 'icon' => '📈', 'portal_backed' => false,
                'title' => 'Capital Markets',
                'summary' => 'Advisory support for securities & investment firms navigating UAE capital markets compliance obligations.',
            ],
            [
                'slug' => 'vasp', 'icon' => '🪙', 'portal_backed' => false,
                'title' => 'Virtual Asset Service Providers',
                'summary' => 'Advisory guidance for crypto & virtual asset businesses on AML frameworks and licensing requirements.',
            ],
        ];
    }

    /**
     * The 3 tech-services tiles: 2 are real, shipped internal products;
     * "Custom Software & App Development" is a general advisory/build offering.
     */
    public static function techServiceTiles(): array
    {
        return [
            [
                'icon' => '🛡️', 'badge' => 'Core Product',
                'title' => 'KYC & Client Onboarding Portal',
                'summary' => 'The platform behind Blue Arrow\'s own compliance service — tokenized self-fill onboarding links, AI-assisted document scanning, and sanctions screening in one place.',
                'features' => [
                    'Tokenized, single-use client self-fill onboarding links — no login required for your customers',
                    'AI-assisted document scanning — auto-extracts names, dates and ID numbers from passports, Emirates IDs and trade licences',
                    'Sector-specific checklists for bullion, real estate, company services and accounting',
                    'Sanctions & PEP screening via Blue Arrow\'s proprietary Sentinel engine',
                    'Risk assessment workflow with periodic review scheduling',
                    'goAML STR/SAR/DPMSR XML report generation for UAE FIU filing',
                ],
            ],
            [
                'icon' => '📊', 'badge' => 'Core Product',
                'title' => 'Bullion Accounting Software',
                'summary' => 'A full double-entry accounting suite built specifically for precious metals trading, with weight-and-purity aware invoicing and inventory.',
                'features' => [
                    'Hierarchical chart of accounts with system and custom accounts',
                    'Double-entry general ledger with balance validation and reversal support',
                    'VAT-aware, metal-priced invoicing — fixed and unfixed (price-TBD) deals, making charges, multi-currency',
                    'Inventory tracking by weight and pieces across gold, silver, platinum and palladium',
                    'Client payments, deposits and margin tracking',
                    'Trial Balance, Balance Sheet, Income Statement, VAT Summary and Client Statement reports — PDF & CSV export',
                ],
            ],
            [
                'icon' => '💻', 'badge' => 'Advisory',
                'title' => 'Custom Software & App Development',
                'summary' => 'Beyond our own products, our team builds bespoke compliance and business software for clients who need something tailored.',
                'features' => [
                    'Custom web portals and internal tools',
                    'Mobile app development',
                    'Integrations with existing business systems',
                    'Ongoing support and maintenance',
                ],
            ],
        ];
    }

    public function home(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('public.home', [
            'services'     => array_slice(static::serviceTiles(), 0, 6),
            'techServices' => static::techServiceTiles(),
            'news'         => NewsItem::published()->latest('published_at')->take(3)->get(),
            'sectors'      => SectorConfig::sectors(),
        ]);
    }

    public function services(): View
    {
        return view('public.services', [
            'services' => static::serviceTiles(),
        ]);
    }

    public function technology(): View
    {
        return view('public.technology', [
            'techServices' => static::techServiceTiles(),
        ]);
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        return redirect()->route('contact')->with('status', 'Thanks — your message has been received. Our team will get back to you shortly.');
    }
}
