<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Policy manuals shelf. Each manual is one entry below; the sidebar and the index page
 * both read this list, so adding a manual is one line plus its content.
 */
class PolicyManualController extends Controller
{
    public const MANUALS = [
        'aml-cft-policy'      => ['AML / CFT / CPF Policy',          'Group-wide policy: governance, MLRO, three lines of defence.'],
        'kyc-cdd-procedures'  => ['KYC & CDD Procedures',            'Onboarding, ongoing due diligence, EDD and simplified measures.'],
        'risk-assessment'     => ['Business Risk Assessment',        'Methodology for customer, product, channel and geography risk.'],
        'sanctions-tfs'       => ['Sanctions & TFS Procedure',       'Screening, EOCN notifications, freezing and reporting.'],
        'reporting-goaml'     => ['STR / goAML Reporting Procedure', 'Escalation, STR/SAR filing and tipping-off controls.'],
        'record-keeping'      => ['Record Keeping & Retention',      'What is kept, where, and for how long.'],
        'training-programme'  => ['Training Programme',              'Staff training plan and attendance records.'],
    ];

    public function index()
    {
        return view('admin.policy-manuals.index', ['manuals' => self::MANUALS]);
    }

    public function show(string $manual)
    {
        abort_unless(isset(self::MANUALS[$manual]), 404);

        return view('admin.policy-manuals.show', [
            'slug'   => $manual,
            'title'  => self::MANUALS[$manual][0],
            'blurb'  => self::MANUALS[$manual][1],
            'manuals' => self::MANUALS,
        ]);
    }
}
