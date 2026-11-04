<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\PolicyManualGenerator;
use Illuminate\Http\Request;

/**
 * Policy manuals shelf. Each manual is one entry below; the sidebar and the index page
 * both read this list, so adding a manual is one line plus its content.
 */
class PolicyManualController extends Controller
{
    public const MANUALS = [
        'aml-cft-policy'      => ['AML Policy: Gold and Bullion Traders', 'AML/CFT/CPF and sanctions manual for dealers in precious metals: governance, CDD, TFS, DPMSR and responsible sourcing. Generates a Word document per client.', 'AML Policy'],
        'aml-real-estate'     => ['AML Policy: Real Estate Brokers and Agents', 'AML/CFT/CPF and sanctions manual for real estate brokers and agents: REAR reporting, source of funds, both sides of the deal. Generates a Word document per client.', 'AML Policy'],
        'aml-csp'             => ['AML Policy: Trust and Company Service Providers', 'AML/CFT/CPF and sanctions manual for company and trust service providers: beneficial ownership, nominee and trust services. Generates a Word document per client.', 'AML Policy'],
        'supply-chain-policy' => ['Supply Chain Policy for Responsible Sourcing', 'OECD five-step policy for gold and bullion traders under Ministerial Decree 68 of 2024. Generates a Word document per client.'],
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

    public function show(Request $request, string $manual)
    {
        abort_unless(isset(self::MANUALS[$manual]), 404);

        $data = [
            'slug'    => $manual,
            'title'   => self::MANUALS[$manual][0],
            'blurb'   => self::MANUALS[$manual][1],
            'manuals' => self::MANUALS,
            'hasTemplate' => PolicyManualGenerator::templatePath($manual) !== null,
        ];

        if ($data['hasTemplate']) {
            $data['tenants'] = Tenant::orderBy('name')->get(['id', 'name']);
            $data['tenant']  = $request->filled('tenant') ? Tenant::find($request->integer('tenant')) : null;
            $data['fields']  = PolicyManualGenerator::FIELDS;
            $data['values']  = $data['tenant'] ? PolicyManualGenerator::values($data['tenant']) : [];
            $data['appointmentOptions'] = PolicyManualGenerator::APPOINTMENT_OPTIONS;
            $gen = $data['tenant'] ? ($data['tenant']->settings['policy_generated'][$manual] ?? null) : null;
            $data['generated'] = $gen;
            $data['templateName'] = basename(PolicyManualGenerator::templatePath($manual));
        }

        return view('admin.policy-manuals.show', $data);
    }

    /** Save the client's details, and optionally download the filled manual. */
    public function generate(Request $request, string $manual, PolicyManualGenerator $generator)
    {
        abort_unless(isset(self::MANUALS[$manual]), 404);
        $template = PolicyManualGenerator::templatePath($manual);
        abort_unless($template, 404);

        $rules = ['tenant_id' => 'required|exists:tenants,id', 'action' => 'nullable|in:save,download'];
        foreach (PolicyManualGenerator::keys() as $k) {
            $rules["f.$k"] = 'nullable|string|max:300';
        }
        $request->validate($rules);

        $tenant = Tenant::findOrFail($request->integer('tenant_id'));

        $profile = [];
        foreach (PolicyManualGenerator::keys() as $k) {
            $profile[$k] = trim((string) $request->input("f.$k", ''));
        }
        $settings = (array) ($tenant->settings ?? []);
        $settings['policy_profile'] = $profile;
        $tenant->settings = $settings;
        $tenant->save();

        if ($request->input('action') !== 'download') {
            return redirect()->route('policy-manuals.show', [$manual, 'tenant' => $tenant->id])
                ->with('success', 'Details saved for ' . $tenant->name . '.');
        }

        $file = $generator->generate($template, PolicyManualGenerator::values($tenant));
        $settings = (array) ($tenant->settings ?? []);
        $settings['policy_generated'][$manual] = ['template' => basename($template), 'at' => now()->toDateTimeString()];
        $tenant->settings = $settings;
        $tenant->save();
        $name = trim(preg_replace('/[^A-Za-z0-9 _-]+/', '', self::MANUALS[$manual][0] . ' - ' . $tenant->name)) . '.docx';

        return response()->download($file, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /** The master template, with placeholders intact. */
    public function template(string $manual)
    {
        $template = PolicyManualGenerator::templatePath($manual);
        abort_unless($template, 404);

        return response()->download($template, basename($template));
    }
}
