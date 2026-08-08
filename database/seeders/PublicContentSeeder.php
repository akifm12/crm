<?php
// database/seeders/PublicContentSeeder.php

namespace Database\Seeders;

use App\Models\ComplianceDeadline;
use App\Models\NewsItem;
use App\Models\ResourceDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PublicContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedComplianceDeadlines();
        $this->seedResources();
        $this->seedNews();
    }

    private function seedComplianceDeadlines(): void
    {
        $rows = [
            ['title' => 'Trade License Renewal', 'description' => 'Renew your DED trade license before expiry to avoid a lapse in your DNFBP registration status.', 'sector' => null, 'category' => 'licensing', 'authority' => 'Dept. of Economic Development', 'recurrence' => 'annual', 'next_due_date' => '2026-11-15'],
            ['title' => 'DNFBP goAML Registration Renewal', 'description' => 'Confirm your goAML reporting entity registration and MLRO details remain current with the UAE Financial Intelligence Unit.', 'sector' => null, 'category' => 'goaml', 'authority' => 'UAE FIU / goAML', 'recurrence' => 'annual', 'next_due_date' => '2026-12-01'],
            ['title' => 'Sanctions & PEP Re-screening of Existing Customers', 'description' => 'Re-screen all existing customers, beneficial owners and transaction counterparties against the latest UN, OFAC, EU and UAE sanctions lists — not just new onboarding.', 'sector' => null, 'category' => 'screening', 'authority' => 'UAE FIU', 'recurrence' => 'ongoing', 'next_due_date' => null],
            ['title' => 'goAML DPMSR Filing — Cash Transactions Above AED 55,000', 'description' => 'File a Designated Precious Metals & Stones Report for any cash transaction at or above the AED 55,000 threshold.', 'sector' => 'gold', 'category' => 'reporting', 'authority' => 'UAE FIU', 'recurrence' => 'ongoing', 'next_due_date' => null],
            ['title' => 'Real Estate Transaction Reporting Above AED 55,000', 'description' => 'Report qualifying real estate transactions above the AED 55,000 threshold per DNFBP obligations.', 'sector' => 'real_estate', 'category' => 'reporting', 'authority' => 'UAE FIU', 'recurrence' => 'ongoing', 'next_due_date' => null],
            ['title' => 'Annual AML/CFT Risk Assessment Review', 'description' => 'Review and update your business-wide AML/CFT risk assessment to reflect current customer, geographic and product risk.', 'sector' => null, 'category' => 'reporting', 'authority' => null, 'recurrence' => 'annual', 'next_due_date' => '2027-01-31'],
            ['title' => 'UBO Register Update', 'description' => 'Confirm beneficial ownership information filed with the registrar is current, including any changes in shareholding structure.', 'sector' => 'company_services', 'category' => 'licensing', 'authority' => null, 'recurrence' => 'annual', 'next_due_date' => '2026-10-01'],
            ['title' => 'Staff AML/CFT Training', 'description' => 'Deliver refresher AML/CFT training to all relevant staff, covering red flags, STR procedures and sanctions obligations.', 'sector' => null, 'category' => 'training', 'authority' => null, 'recurrence' => 'annual', 'next_due_date' => '2026-09-30'],
            ['title' => 'Suspicious Transaction Report (STR) Filing', 'description' => 'File an STR with the FIU as soon as reasonable suspicion of money laundering or terrorist financing arises — this is an ongoing, trigger-based obligation, not a fixed date.', 'sector' => null, 'category' => 'reporting', 'authority' => 'UAE FIU', 'recurrence' => 'ongoing', 'next_due_date' => null],
            ['title' => 'Ejari Contract Renewal', 'description' => 'Renew Ejari tenancy contracts tied to your registered business premises.', 'sector' => 'real_estate', 'category' => 'licensing', 'authority' => null, 'recurrence' => 'annual', 'next_due_date' => '2026-10-20'],
            ['title' => 'CAHRA List Review', 'description' => 'Review your Countries with AML/CFT deficiencies (CAHRA) list against the latest FATF and local guidance, and reassess exposed clients.', 'sector' => null, 'category' => 'screening', 'authority' => null, 'recurrence' => 'quarterly', 'next_due_date' => '2026-10-01'],
            ['title' => 'Client & Shareholder ID Expiry Monitoring', 'description' => 'Track passport, Emirates ID and trade license expiries for clients, shareholders and signatories, and request renewals before lapse.', 'sector' => null, 'category' => 'screening', 'authority' => null, 'recurrence' => 'ongoing', 'next_due_date' => null],
        ];

        foreach ($rows as $i => $row) {
            ComplianceDeadline::create($row + ['source_url' => null, 'sort_order' => $i]);
        }
    }

    private function seedResources(): void
    {
        $docs = [
            [
                'title' => 'DNFBP AML Compliance Checklist',
                'description' => 'A general checklist covering the core AML/CFT program elements every UAE DNFBP should have in place.',
                'sector' => null,
                'category' => 'checklist',
                'lead' => 'Use this checklist as a starting point to assess whether your business has the core building blocks of an AML/CFT compliance program in place.',
                'sections' => [
                    ['heading' => 'Governance', 'items' => [
                        'A designated Money Laundering Reporting Officer (MLRO) is appointed and registered',
                        'A board/owner-approved AML/CFT policy exists and is reviewed annually',
                        'Roles and responsibilities for compliance are documented',
                    ]],
                    ['heading' => 'Customer Due Diligence', 'items' => [
                        'KYC is completed for all new customers before onboarding',
                        'Enhanced due diligence procedures exist for high-risk customers and PEPs',
                        'Beneficial ownership (UBO) is identified and verified for corporate customers',
                    ]],
                    ['heading' => 'Screening & Monitoring', 'items' => [
                        'Customers are screened against sanctions and PEP lists at onboarding',
                        'Existing customers are periodically re-screened, not just at onboarding',
                        'Unusual or suspicious transactions are flagged and reviewed',
                    ]],
                    ['heading' => 'Reporting', 'items' => [
                        'goAML registration is active and reporting entity details are current',
                        'STR/SAR filing procedures are documented and staff know when to escalate',
                        'Records are retained for the statutory minimum period',
                    ]],
                    ['heading' => 'Training', 'items' => [
                        'All relevant staff receive AML/CFT training at least annually',
                        'Training covers red flags specific to your sector',
                    ]],
                ],
            ],
            [
                'title' => 'goAML Reporting Threshold Quick Reference',
                'description' => 'A quick reference for the AED 55,000 DPMSR / transaction reporting threshold that applies across DNFBP sectors.',
                'sector' => null,
                'category' => 'reference',
                'lead' => 'Across the DNFBP sectors Blue Arrow supports, cash transactions at or above AED 55,000 generally trigger a reporting obligation via goAML. This reference summarizes how that applies by sector.',
                'sections' => [
                    ['heading' => 'Precious Metals & Stones (Gold)', 'items' => [
                        'Reporting trigger: cash sale at or above AED 55,000',
                        'Report type: Designated Precious Metals & Stones Report (DPMSR)',
                        'Applies per transaction, including linked/structured transactions',
                    ]],
                    ['heading' => 'Real Estate', 'items' => [
                        'Reporting trigger: qualifying transaction at or above AED 55,000',
                        'Source of funds must be verified and documented',
                    ]],
                    ['heading' => 'Company Service Providers & Accounting', 'items' => [
                        'Reporting trigger: transaction at or above AED 55,000',
                        'Applies alongside standard UBO and source-of-funds obligations',
                    ]],
                    ['heading' => 'Beyond the threshold', 'items' => [
                        'Suspicious transactions must be reported regardless of value via an STR',
                        'This threshold is general guidance only — always confirm current requirements with your MLRO',
                    ]],
                ],
            ],
            [
                'title' => 'UBO Declaration Checklist',
                'description' => 'Key items to verify when collecting beneficial ownership declarations from corporate clients.',
                'sector' => 'company_services',
                'category' => 'checklist',
                'lead' => 'Beneficial ownership verification is a core requirement for company service providers. Use this checklist when collecting UBO declarations from corporate clients.',
                'sections' => [
                    ['heading' => 'Identification', 'items' => [
                        'Full legal name, nationality and date of birth of each UBO captured',
                        'Ownership/control percentage documented for each UBO',
                        'Ownership structure chart obtained for multi-layer entities',
                    ]],
                    ['heading' => 'Verification', 'items' => [
                        'Valid passport/Emirates ID collected and checked for expiry',
                        'PEP status declared and screened',
                        'Sanctions screening completed for each UBO',
                    ]],
                    ['heading' => 'Ongoing', 'items' => [
                        'UBO information is refreshed when ownership changes',
                        'Annual confirmation of UBO details obtained from the client',
                    ]],
                ],
            ],
            [
                'title' => 'Sanctions Screening Quick Guide',
                'description' => 'A practical guide to running effective sanctions and PEP screening for DNFBP customers.',
                'sector' => null,
                'category' => 'guide',
                'lead' => 'Sanctions screening is a frontline AML control. This guide covers what effective screening looks like in practice.',
                'sections' => [
                    ['heading' => 'When to screen', 'items' => [
                        'At onboarding, before any transaction takes place',
                        'On an ongoing basis against updated sanctions lists',
                        'Immediately when a new sanctions list update is issued',
                    ]],
                    ['heading' => 'Who to screen', 'items' => [
                        'The customer (individual or corporate entity)',
                        'Beneficial owners and authorized signatories',
                        'Counterparties in higher-risk transactions',
                    ]],
                    ['heading' => 'What to check against', 'items' => [
                        'UN Consolidated Sanctions List',
                        'Local UAE sanctions list',
                        'Relevant international lists (OFAC, EU) where applicable to your risk profile',
                    ]],
                    ['heading' => 'Handling a match', 'items' => [
                        'Do not proceed with the transaction until the match is resolved',
                        'Escalate true matches to your MLRO immediately',
                        'Document the review outcome, even for false positives',
                    ]],
                ],
            ],
            [
                'title' => 'Bullion Client Onboarding Document Checklist',
                'description' => 'Documents typically required to onboard a corporate or individual bullion trading client.',
                'sector' => 'gold',
                'category' => 'checklist',
                'lead' => 'Use this checklist when onboarding precious metals and stones trading clients, corporate or individual.',
                'sections' => [
                    ['heading' => 'Corporate clients', 'items' => [
                        'Trade licence (valid, with expiry tracked)',
                        'Memorandum of Association',
                        'Passport and Emirates ID for authorized signatories',
                        'Source of funds evidence',
                        'Ejari contract, where applicable',
                    ]],
                    ['heading' => 'Individual clients', 'items' => [
                        'Passport',
                        'Emirates ID',
                        'Source of funds evidence',
                    ]],
                    ['heading' => 'Declarations', 'items' => [
                        'PEP status declaration',
                        'Sanctions declaration',
                        'Supply chain declaration',
                        'CAHRA (high-risk country) declaration where relevant',
                    ]],
                ],
            ],
            [
                'title' => 'Real Estate Client Due Diligence Checklist',
                'description' => 'Key due diligence steps for real estate brokers onboarding buyers, sellers and tenants.',
                'sector' => 'real_estate',
                'category' => 'checklist',
                'lead' => 'This checklist covers the due diligence steps real estate brokers should complete before facilitating a property transaction.',
                'sections' => [
                    ['heading' => 'Client identification', 'items' => [
                        'Passport / Emirates ID collected and verified',
                        'Trade licence collected for corporate clients',
                        'Beneficial ownership identified for corporate buyers/sellers',
                    ]],
                    ['heading' => 'Transaction details', 'items' => [
                        'Property type, location and value recorded',
                        'Purpose of transaction documented (own use, investment, resale)',
                        'RERA registration details captured where applicable',
                    ]],
                    ['heading' => 'Source of funds', 'items' => [
                        'Source of funds evidence obtained and assessed for consistency',
                        'Mortgage / bank financing documentation collected where used',
                    ]],
                ],
            ],
        ];

        foreach ($docs as $doc) {
            $pdf = Pdf::loadView('public.pdf.resource', [
                'title' => $doc['title'],
                'lead' => $doc['lead'],
                'sections' => $doc['sections'],
            ]);

            $filename = 'resources/' . str($doc['title'])->slug() . '.pdf';
            $content = $pdf->output();
            Storage::disk('public')->put($filename, $content);

            ResourceDocument::create([
                'title' => $doc['title'],
                'description' => $doc['description'],
                'sector' => $doc['sector'],
                'category' => $doc['category'],
                'file_path' => $filename,
                'file_size' => strlen($content),
                'is_published' => true,
            ]);
        }
    }

    private function seedNews(): void
    {
        $items = [
            [
                'title' => 'Why Ongoing Sanctions Re-Screening Matters, Not Just Onboarding',
                'summary' => 'A one-time sanctions check at onboarding isn\'t enough — sanctions lists change continuously, and so should your screening.',
                'body' => "Many DNFBPs treat sanctions screening as a one-time onboarding step. In practice, UN, OFAC, EU and UAE sanctions lists are updated frequently, and a customer who was clear six months ago may not be clear today.\n\nBest practice is to re-screen existing customers, beneficial owners and counterparties on a recurring basis — not just when a new relationship begins. This is especially important for higher-risk customer segments and for jurisdictions or individuals connected to CAHRA (Countries with AML/CFT deficiencies).\n\nA practical starting point: re-screen your full customer book at least quarterly, and immediately after any major sanctions list update is issued. Document each screening run, even when the result is clear, so you can demonstrate ongoing due diligence to your regulator.",
                'category' => 'aml',
            ],
            [
                'title' => 'The AED 55,000 Threshold: What UAE DNFBPs Need to Know',
                'summary' => 'A plain-English explanation of the AED 55,000 reporting threshold that applies across bullion, real estate, company services and accounting.',
                'body' => "If you operate in a UAE DNFBP sector, you've likely encountered the AED 55,000 reporting threshold. Here's what it generally means in practice.\n\nTransactions at or above this value — whether a bullion sale, a real estate transaction, or another qualifying activity — typically trigger a reporting obligation via the goAML system, most commonly as a Designated Precious Metals & Stones Report (DPMSR) for gold and precious metals dealers, or equivalent reporting for other sectors.\n\nImportantly, this threshold does not limit your obligation to report suspicious activity. Any transaction, regardless of value, that raises reasonable suspicion of money laundering or terrorist financing should be escalated as a Suspicious Transaction Report (STR), separate from any threshold-based reporting.\n\nAs always, confirm the specifics for your business with your MLRO or compliance advisor, since requirements can vary by license type and activity.",
                'category' => 'regulatory',
            ],
            [
                'title' => 'Common UBO Declaration Mistakes Company Service Providers Make',
                'summary' => 'Beneficial ownership verification is a recurring weak point in AML programs — here are the most common gaps.',
                'body' => "Beneficial ownership (UBO) verification is one of the most frequently cited gaps in DNFBP compliance reviews. A few patterns show up repeatedly:\n\nFirst, incomplete ownership chains — businesses stop tracing ownership at the first corporate layer instead of tracing through to the natural person who ultimately owns or controls the entity.\n\nSecond, stale information — UBO details are captured once at onboarding and never refreshed, even when ownership structures change.\n\nThird, missing PEP and sanctions screening at the UBO level — screening is applied to the company itself but not extended to each identified beneficial owner.\n\nA simple fix: build UBO refresh into your annual client review cycle, and make sure your onboarding checklist explicitly requires tracing ownership to natural persons, not just the immediate shareholder.",
                'category' => 'insight',
            ],
            [
                'title' => 'Building a Practical AML Training Cadence for Small DNFBPs',
                'summary' => 'You don\'t need a large compliance team to run effective AML training — here\'s a lightweight approach that works.',
                'body' => "Smaller DNFBPs often assume meaningful AML training requires a dedicated compliance department. In practice, a lightweight but consistent approach is usually more effective than an elaborate program run inconsistently.\n\nA practical cadence: a full refresher session annually covering your sector's specific red flags, plus short update briefings whenever a relevant regulatory change occurs (a new sanctions designation, an updated CAHRA list, or a threshold change).\n\nKeep a simple training log — who attended, when, and what was covered — since this is often one of the first things reviewed in a compliance inspection. Training doesn't need to be lengthy to be effective; a focused 45-minute session covering real scenarios your staff are likely to encounter tends to stick better than a lengthy generic course.",
                'category' => 'insight',
            ],
        ];

        foreach ($items as $i => $item) {
            NewsItem::create($item + [
                'source_name' => 'BA-Digest',
                'source_url' => null,
                'origin' => 'ai_digest',
                'published_at' => now()->subDays(count($items) - $i),
                'is_published' => true,
            ]);
        }
    }
}
