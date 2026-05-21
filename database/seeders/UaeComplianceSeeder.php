<?php

namespace Database\Seeders;

use App\Models\ComplianceDeadline;
use App\Models\ComplianceRequirement;
use App\Models\LicenseActivity;
use App\Models\Regulator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UaeComplianceSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRegulators();
        $this->seedActivities();
        $this->seedRequirements();
        $this->generateDeadlines(2026);
        $this->generateDeadlines(2027);
    }

    private function seedRegulators(): void
    {
        $regulators = [
            // Financial Services
            [
                'name' => 'Central Bank of the UAE',
                'acronym' => 'CBUAE',
                'sector' => 'financial',
                'description' => 'Federal regulator for banks, exchange houses, finance companies, payment service providers and insurance brokers in the UAE.',
                'website' => 'https://www.centralbank.ae',
                'jurisdiction' => 'UAE Federal',
            ],
            [
                'name' => 'Securities and Commodities Authority',
                'acronym' => 'SCA',
                'sector' => 'financial',
                'description' => 'Federal regulator for securities, commodities, and derivatives markets across the UAE (excluding DIFC and ADGM).',
                'website' => 'https://www.sca.gov.ae',
                'jurisdiction' => 'UAE Federal',
            ],
            [
                'name' => 'Dubai Financial Services Authority',
                'acronym' => 'DFSA',
                'sector' => 'financial',
                'description' => 'Independent regulator of financial services conducted in or from the Dubai International Financial Centre (DIFC).',
                'website' => 'https://www.dfsa.ae',
                'jurisdiction' => 'DIFC, Dubai',
            ],
            [
                'name' => 'Financial Services Regulatory Authority',
                'acronym' => 'FSRA',
                'sector' => 'financial',
                'description' => 'Regulator of financial services conducted in or from the Abu Dhabi Global Market (ADGM).',
                'website' => 'https://www.adgm.com/fsra',
                'jurisdiction' => 'ADGM, Abu Dhabi',
            ],
            [
                'name' => 'Insurance Authority (IA)',
                'acronym' => 'IA-UAE',
                'sector' => 'financial',
                'description' => 'Federal regulator for all insurance activities and companies operating in the UAE.',
                'website' => 'https://www.ia.gov.ae',
                'jurisdiction' => 'UAE Federal',
            ],
            // Real Estate
            [
                'name' => 'Real Estate Regulatory Agency',
                'acronym' => 'RERA',
                'sector' => 'real_estate',
                'description' => 'Regulatory arm of Dubai Land Department governing real estate developers, brokers, and property managers in Dubai.',
                'website' => 'https://www.rera.gov.ae',
                'jurisdiction' => 'Dubai',
            ],
            [
                'name' => 'Dubai Land Department',
                'acronym' => 'DLD',
                'sector' => 'real_estate',
                'description' => 'Government entity responsible for real estate registration, transactions, and regulatory oversight in Dubai.',
                'website' => 'https://www.dubailand.gov.ae',
                'jurisdiction' => 'Dubai',
            ],
            [
                'name' => 'Abu Dhabi Real Estate Centre',
                'acronym' => 'ADREC',
                'sector' => 'real_estate',
                'description' => 'Abu Dhabi\'s real estate regulatory body overseeing developers, brokers and property management in Abu Dhabi.',
                'website' => 'https://www.adrec.ae',
                'jurisdiction' => 'Abu Dhabi',
            ],
        ];

        foreach ($regulators as $data) {
            Regulator::firstOrCreate(['acronym' => $data['acronym']], $data);
        }
    }

    private function seedActivities(): void
    {
        $cbuae = Regulator::where('acronym', 'CBUAE')->first();
        $sca = Regulator::where('acronym', 'SCA')->first();
        $dfsa = Regulator::where('acronym', 'DFSA')->first();
        $fsra = Regulator::where('acronym', 'FSRA')->first();
        $ia = Regulator::where('acronym', 'IA-UAE')->first();
        $rera = Regulator::where('acronym', 'RERA')->first();
        $dld = Regulator::where('acronym', 'DLD')->first();
        $adrec = Regulator::where('acronym', 'ADREC')->first();

        $activities = [
            // CBUAE activities
            ['name' => 'Commercial Banking', 'sector' => 'financial', 'regulator_id' => $cbuae->id, 'description' => 'Licensed commercial bank accepting deposits and providing loans.'],
            ['name' => 'Islamic Banking', 'sector' => 'financial', 'regulator_id' => $cbuae->id, 'description' => 'Sharia-compliant banking operations.'],
            ['name' => 'Exchange House', 'sector' => 'financial', 'regulator_id' => $cbuae->id, 'description' => 'Money exchange and remittance services.'],
            ['name' => 'Finance Company', 'sector' => 'financial', 'regulator_id' => $cbuae->id, 'description' => 'Consumer and corporate finance excluding deposit-taking.'],
            ['name' => 'Payment Service Provider', 'sector' => 'financial', 'regulator_id' => $cbuae->id, 'description' => 'Licensed payment processing, wallets and stored value facilities.'],
            // SCA activities
            ['name' => 'Investment Manager', 'sector' => 'financial', 'regulator_id' => $sca->id, 'description' => 'Manages client portfolios and investment funds.'],
            ['name' => 'Broker / Dealer', 'sector' => 'financial', 'regulator_id' => $sca->id, 'description' => 'Executes securities and commodities transactions on behalf of clients.'],
            ['name' => 'Investment Advisor', 'sector' => 'financial', 'regulator_id' => $sca->id, 'description' => 'Provides investment advice to retail and professional clients.'],
            ['name' => 'Collective Investment Scheme Operator', 'sector' => 'financial', 'regulator_id' => $sca->id, 'description' => 'Manages and administers registered investment funds.'],
            // DFSA activities
            ['name' => 'Authorised Firm – Banking', 'sector' => 'financial', 'regulator_id' => $dfsa->id, 'description' => 'Banking business conducted in or from the DIFC.'],
            ['name' => 'Authorised Firm – Arranging Deals', 'sector' => 'financial', 'regulator_id' => $dfsa->id, 'description' => 'Arranging deals in investments in the DIFC.'],
            ['name' => 'Authorised Firm – Managing Assets', 'sector' => 'financial', 'regulator_id' => $dfsa->id, 'description' => 'Discretionary management of client assets in the DIFC.'],
            ['name' => 'Authorised Firm – Insurance', 'sector' => 'financial', 'regulator_id' => $dfsa->id, 'description' => 'Insurance activities licensed by the DFSA in the DIFC.'],
            // FSRA activities
            ['name' => 'Authorised Person – Banking', 'sector' => 'financial', 'regulator_id' => $fsra->id, 'description' => 'Banking activities in or from the ADGM.'],
            ['name' => 'Authorised Person – Fund Management', 'sector' => 'financial', 'regulator_id' => $fsra->id, 'description' => 'Managing collective investment funds in the ADGM.'],
            ['name' => 'Authorised Person – Dealing in Securities', 'sector' => 'financial', 'regulator_id' => $fsra->id, 'description' => 'Dealing as principal or agent in securities in the ADGM.'],
            // Insurance Authority activities
            ['name' => 'Insurance Company', 'sector' => 'financial', 'regulator_id' => $ia->id, 'description' => 'Life or non-life insurance company licensed under federal law.'],
            ['name' => 'Insurance Broker', 'sector' => 'financial', 'regulator_id' => $ia->id, 'description' => 'Licensed insurance brokerage intermediary.'],
            // RERA activities
            ['name' => 'Real Estate Developer', 'sector' => 'real_estate', 'regulator_id' => $rera->id, 'description' => 'Registered developer of off-plan or completed real estate projects in Dubai.'],
            ['name' => 'Real Estate Broker', 'sector' => 'real_estate', 'regulator_id' => $rera->id, 'description' => 'Licensed real estate agent and brokerage firm in Dubai.'],
            ['name' => 'Property Management Company', 'sector' => 'real_estate', 'regulator_id' => $rera->id, 'description' => 'Manages residential or commercial properties on behalf of owners.'],
            ['name' => 'Real Estate Valuator', 'sector' => 'real_estate', 'regulator_id' => $dld->id, 'description' => 'Certified property valuation professional registered with DLD.'],
            // ADREC activities
            ['name' => 'Real Estate Developer (Abu Dhabi)', 'sector' => 'real_estate', 'regulator_id' => $adrec->id, 'description' => 'Licensed property developer in Abu Dhabi.'],
            ['name' => 'Real Estate Broker (Abu Dhabi)', 'sector' => 'real_estate', 'regulator_id' => $adrec->id, 'description' => 'Licensed real estate broker in Abu Dhabi.'],
        ];

        foreach ($activities as $data) {
            LicenseActivity::firstOrCreate(
                ['name' => $data['name'], 'suggested_regulator_id' => $data['regulator_id']],
                [
                    'description' => $data['description'],
                    'sector' => $data['sector'],
                    'suggested_regulator_id' => $data['regulator_id'],
                ]
            );
        }
    }

    private function seedRequirements(): void
    {
        $reqs = $this->requirementsData();

        foreach ($reqs as $data) {
            $regulator = Regulator::where('acronym', $data['regulator'])->first();
            if (! $regulator) {
                continue;
            }

            $activityId = null;
            if (isset($data['activity'])) {
                $activity = LicenseActivity::where('name', $data['activity'])
                    ->where('suggested_regulator_id', $regulator->id)
                    ->first();
                $activityId = $activity?->id;
            }

            ComplianceRequirement::firstOrCreate(
                ['title' => $data['title'], 'regulator_id' => $regulator->id],
                [
                    'description' => $data['description'],
                    'license_activity_id' => $activityId,
                    'frequency' => $data['frequency'],
                    'category' => $data['category'] ?? 'Reporting',
                    'submission_channel' => $data['channel'] ?? null,
                    'penalty_note' => $data['penalty'] ?? null,
                ]
            );
        }
    }

    private function requirementsData(): array
    {
        return [
            // ── CBUAE ──────────────────────────────────────────────────────────────
            [
                'regulator' => 'CBUAE',
                'title' => 'Monthly Liquidity Coverage Ratio (LCR) Return',
                'description' => 'Submit the Liquidity Coverage Ratio report to CBUAE via the Regulatory Reporting System (RRS). Banks must maintain an LCR of at least 100% at all times.',
                'frequency' => 'monthly',
                'category' => 'Prudential Reporting',
                'channel' => 'CBUAE Regulatory Reporting System (RRS)',
                'penalty' => 'Administrative sanctions including financial penalties up to AED 50 million for persistent non-compliance (CBUAE Decision No. 27/2017).',
            ],
            [
                'regulator' => 'CBUAE',
                'title' => 'Quarterly Capital Adequacy Ratio (CAR) Return',
                'description' => 'Submit Capital Adequacy Ratio (Basel III) returns including Pillar 1 capital requirements for credit, market and operational risk.',
                'frequency' => 'quarterly',
                'category' => 'Prudential Reporting',
                'channel' => 'CBUAE Regulatory Reporting System (RRS)',
                'penalty' => 'Non-compliance may trigger supervisory intervention; failure to maintain minimum 13% CAR may result in restrictions on dividends and capital distributions.',
            ],
            [
                'regulator' => 'CBUAE',
                'title' => 'Annual Audited Financial Statements Submission',
                'description' => 'Submit audited annual financial statements prepared under IFRS, signed by the external auditor, within 4 months of financial year-end.',
                'frequency' => 'annual',
                'category' => 'Reporting',
                'channel' => 'CBUAE Regulatory Reporting System (RRS)',
                'penalty' => 'Late submission may result in administrative fines and public disclosure.',
            ],
            [
                'regulator' => 'CBUAE',
                'title' => 'Annual AML/CFT Compliance Report',
                'description' => 'Submit the annual Anti-Money Laundering and Countering the Financing of Terrorism compliance report detailing risk assessments, suspicious transaction reports, staff training, and programme effectiveness.',
                'frequency' => 'annual',
                'category' => 'AML/CFT',
                'channel' => 'goAML system (Financial Intelligence Unit)',
                'penalty' => 'Violations of AML obligations may result in fines up to AED 1 million per violation under Federal Decree-Law No. 20/2018.',
            ],
            [
                'regulator' => 'CBUAE',
                'title' => 'Monthly Net Stable Funding Ratio (NSFR) Return',
                'description' => 'Report the Net Stable Funding Ratio to ensure stable funding over a one-year horizon. Minimum NSFR of 100% required.',
                'frequency' => 'monthly',
                'category' => 'Prudential Reporting',
                'channel' => 'CBUAE Regulatory Reporting System (RRS)',
            ],
            [
                'regulator' => 'CBUAE',
                'title' => 'Quarterly Large Exposures Return',
                'description' => 'Report all exposures exceeding 10% of Tier 1 capital to a single counterparty or connected group. Maximum single exposure limit is 25% of Tier 1.',
                'frequency' => 'quarterly',
                'category' => 'Prudential Reporting',
                'channel' => 'CBUAE Regulatory Reporting System (RRS)',
            ],
            [
                'regulator' => 'CBUAE',
                'title' => 'Annual Compliance Officer Report',
                'description' => 'The Compliance Officer must submit an annual report to the Board of Directors covering compliance programme status, key findings, remediation actions, and regulatory developments.',
                'frequency' => 'annual',
                'category' => 'Governance',
                'channel' => 'Board submission with copy to CBUAE upon request',
            ],
            [
                'regulator' => 'CBUAE',
                'title' => 'Quarterly Credit Risk Report',
                'description' => 'Submit reports on loan portfolio quality, non-performing loans, provisions and impairments in accordance with IFRS 9 and CBUAE Credit Risk Guidelines.',
                'frequency' => 'quarterly',
                'category' => 'Prudential Reporting',
                'channel' => 'CBUAE Regulatory Reporting System (RRS)',
            ],
            // Exchange House specific
            [
                'regulator' => 'CBUAE',
                'activity' => 'Exchange House',
                'title' => 'Monthly Transaction Volume Report',
                'description' => 'Report total remittance and exchange transaction volumes, broken down by corridor and currency, to CBUAE.',
                'frequency' => 'monthly',
                'category' => 'Statistical Reporting',
                'channel' => 'CBUAE Regulatory Reporting System (RRS)',
            ],
            [
                'regulator' => 'CBUAE',
                'activity' => 'Exchange House',
                'title' => 'Annual Licence Renewal',
                'description' => 'Renew the exchange house operating licence with CBUAE. Submit renewal application with updated KYC, financial statements and compliance attestations.',
                'frequency' => 'annual',
                'category' => 'Licensing',
                'channel' => 'CBUAE Online Portal',
                'penalty' => 'Operating without a valid licence is a criminal offence under the CBUAE Law.',
            ],

            // ── SCA ────────────────────────────────────────────────────────────────
            [
                'regulator' => 'SCA',
                'title' => 'Quarterly Financial Condition Report',
                'description' => 'Submit quarterly financial statements showing net capital, client money balances, and financial condition to SCA.',
                'frequency' => 'quarterly',
                'category' => 'Prudential Reporting',
                'channel' => 'SCA E-Services Portal',
                'penalty' => 'Administrative fine of AED 10,000–500,000 for late or inaccurate submission.',
            ],
            [
                'regulator' => 'SCA',
                'title' => 'Annual Audited Financial Statements',
                'description' => 'Submit externally audited annual financial statements to SCA within 3 months of the financial year-end.',
                'frequency' => 'annual',
                'category' => 'Reporting',
                'channel' => 'SCA E-Services Portal',
                'penalty' => 'Fine and potential suspension of licence for persistent late submission.',
            ],
            [
                'regulator' => 'SCA',
                'title' => 'Annual Compliance Officer Report',
                'description' => 'Compliance Officer submits an annual report to the Board and SCA covering programme effectiveness, breaches, and remediation.',
                'frequency' => 'annual',
                'category' => 'Governance',
                'channel' => 'SCA E-Services Portal',
            ],
            [
                'regulator' => 'SCA',
                'title' => 'Annual AML/CFT Report',
                'description' => 'Submit the annual AML/CFT compliance report to SCA covering risk assessment results, STR statistics, training records, and programme enhancements.',
                'frequency' => 'annual',
                'category' => 'AML/CFT',
                'channel' => 'SCA E-Services Portal & goAML',
            ],
            [
                'regulator' => 'SCA',
                'title' => 'Annual Licence Renewal',
                'description' => 'Renew SCA operating licence annually. Submit fit and proper forms for key persons, updated business plan, and financial standing evidence.',
                'frequency' => 'annual',
                'category' => 'Licensing',
                'channel' => 'SCA E-Services Portal',
                'penalty' => 'Licence lapses if not renewed by deadline. Operating with lapsed licence results in criminal liability.',
            ],
            [
                'regulator' => 'SCA',
                'title' => 'Monthly Client Money Reconciliation Report',
                'description' => 'Submit monthly client money reconciliation confirming segregation of client assets from firm assets.',
                'frequency' => 'monthly',
                'category' => 'Client Assets',
                'channel' => 'SCA E-Services Portal',
            ],

            // ── DFSA ───────────────────────────────────────────────────────────────
            [
                'regulator' => 'DFSA',
                'title' => 'Quarterly Prudential Return (PIB/PII)',
                'description' => 'Submit the quarterly Prudential Investment Business (PIB) or Prudential Insurance Business (PII) return to DFSA via the online system. Covers capital adequacy, liquidity, and risk exposures.',
                'frequency' => 'quarterly',
                'category' => 'Prudential Reporting',
                'channel' => 'DFSA Online Regulatory System',
                'penalty' => 'DFSA may impose financial penalties up to USD 500,000 and take enforcement action for late or inaccurate returns.',
            ],
            [
                'regulator' => 'DFSA',
                'title' => 'Annual Audited Financial Accounts',
                'description' => 'File audited annual accounts with the DFSA within 4 months of financial year-end. Accounts must comply with IFRS as adopted in the DIFC.',
                'frequency' => 'annual',
                'category' => 'Reporting',
                'channel' => 'DFSA Online Regulatory System',
            ],
            [
                'regulator' => 'DFSA',
                'title' => 'Annual Compliance Report',
                'description' => 'The Senior Executive Officer (SEO) or Compliance Officer submits an annual compliance report to the DFSA within 4 months of year-end covering all material compliance matters.',
                'frequency' => 'annual',
                'category' => 'Governance',
                'channel' => 'DFSA Online Regulatory System',
                'penalty' => 'Failure to submit is a contravention that may result in a public censure or fine.',
            ],
            [
                'regulator' => 'DFSA',
                'title' => 'Annual AML Return',
                'description' => 'Submit the annual AML return to DFSA detailing customer risk profile, STR/SAR statistics, and AML programme enhancements.',
                'frequency' => 'annual',
                'category' => 'AML/CFT',
                'channel' => 'DFSA Online Regulatory System',
            ],
            [
                'regulator' => 'DFSA',
                'title' => 'Annual Authorised Firm Licence Renewal',
                'description' => 'Pay the annual licence fee and confirm material information about the firm, its key persons, and controlled functions with the DFSA.',
                'frequency' => 'annual',
                'category' => 'Licensing',
                'channel' => 'DFSA Online Regulatory System',
                'penalty' => 'Operating without a valid DFSA licence in the DIFC is a criminal offence under DIFC Law No. 1 of 2004.',
            ],
            [
                'regulator' => 'DFSA',
                'title' => 'Semi-Annual Large Exposure Report',
                'description' => 'Report all exposures exceeding the DFSA\'s large exposure thresholds twice per year.',
                'frequency' => 'semi_annual',
                'category' => 'Prudential Reporting',
                'channel' => 'DFSA Online Regulatory System',
            ],

            // ── FSRA ───────────────────────────────────────────────────────────────
            [
                'regulator' => 'FSRA',
                'title' => 'Quarterly Prudential Return',
                'description' => 'Submit quarterly capital adequacy and liquidity return to FSRA covering minimum capital requirements, liquid assets, and risk exposures.',
                'frequency' => 'quarterly',
                'category' => 'Prudential Reporting',
                'channel' => 'ADGM Online Portal',
                'penalty' => 'FSRA may impose a financial penalty and/or restrict regulated activities for late or incomplete submissions.',
            ],
            [
                'regulator' => 'FSRA',
                'title' => 'Annual Audited Financial Statements',
                'description' => 'File IFRS-compliant audited annual accounts with FSRA within 4 months of year-end.',
                'frequency' => 'annual',
                'category' => 'Reporting',
                'channel' => 'ADGM Online Portal',
            ],
            [
                'regulator' => 'FSRA',
                'title' => 'Annual Compliance Report',
                'description' => 'Submit the annual compliance report to FSRA summarising the effectiveness of compliance arrangements and any material breaches or incidents.',
                'frequency' => 'annual',
                'category' => 'Governance',
                'channel' => 'ADGM Online Portal',
            ],
            [
                'regulator' => 'FSRA',
                'title' => 'Annual AML/CFT Return',
                'description' => 'Complete and submit the FSRA\'s annual AML/CFT return covering customer risk profiling, STR data, and programme assessment.',
                'frequency' => 'annual',
                'category' => 'AML/CFT',
                'channel' => 'ADGM Online Portal',
            ],
            [
                'regulator' => 'FSRA',
                'title' => 'Annual Licence Fee and Renewal',
                'description' => 'Pay annual FSRA licence fee and confirm continuing fitness and propriety of key persons and controlled functions.',
                'frequency' => 'annual',
                'category' => 'Licensing',
                'channel' => 'ADGM Online Portal',
            ],

            // ── RERA / Real Estate ─────────────────────────────────────────────────
            [
                'regulator' => 'RERA',
                'title' => 'Annual Broker Licence Renewal (Trakheesi)',
                'description' => 'Renew the real estate broker registration and individual broker cards via the Trakheesi system. Requires completion of mandatory DREI training course.',
                'frequency' => 'annual',
                'category' => 'Licensing',
                'channel' => 'Trakheesi (DLD / RERA) Portal',
                'penalty' => 'Operating without a valid broker licence is subject to fines up to AED 50,000 and blacklisting.',
            ],
            [
                'regulator' => 'RERA',
                'title' => 'Annual DREI Continuing Education (CPD)',
                'description' => 'Real estate brokers must complete the Dubai Real Estate Institute (DREI) mandatory continuing professional development hours annually for licence renewal.',
                'frequency' => 'annual',
                'category' => 'Training',
                'channel' => 'Dubai Real Estate Institute (DREI)',
                'penalty' => 'Failure to complete CPD prevents licence renewal.',
            ],
            [
                'regulator' => 'RERA',
                'activity' => 'Real Estate Developer',
                'title' => 'Quarterly Escrow Account Report',
                'description' => 'Developers must submit quarterly reports on off-plan project escrow accounts to RERA, confirming balances, withdrawals, and project construction progress milestones.',
                'frequency' => 'quarterly',
                'category' => 'Escrow Reporting',
                'channel' => 'RERA Developer Portal (Oqood)',
                'penalty' => 'Administrative fines and potential project freeze for non-compliance with escrow regulations under Law No. 8 of 2007.',
            ],
            [
                'regulator' => 'RERA',
                'activity' => 'Real Estate Developer',
                'title' => 'Annual Project Progress Report',
                'description' => 'Submit annual project status update to RERA including construction milestones achieved, unit sales, and escrow utilisation versus project budget.',
                'frequency' => 'annual',
                'category' => 'Project Reporting',
                'channel' => 'RERA Developer Portal (Oqood)',
            ],
            [
                'regulator' => 'RERA',
                'activity' => 'Property Management Company',
                'title' => 'Annual Service Charge Budget Filing',
                'description' => 'Submit the annual service charge budget for managed communities to RERA for approval before the start of each fiscal year.',
                'frequency' => 'annual',
                'category' => 'Financial Reporting',
                'channel' => 'Mollak System (RERA)',
                'penalty' => 'Service charge invoices cannot be issued to owners without RERA-approved budget.',
            ],
            [
                'regulator' => 'DLD',
                'title' => 'Annual Valuator Registration Renewal',
                'description' => 'Renew certified valuator registration with the Dubai Land Department. Must demonstrate active valuation practice and CPD compliance.',
                'frequency' => 'annual',
                'category' => 'Licensing',
                'channel' => 'DLD Online Portal',
            ],
        ];
    }

    private function generateDeadlines(int $year): void
    {
        $requirements = ComplianceRequirement::with('regulator')->get();

        foreach ($requirements as $req) {
            $deadlines = $this->computeDeadlines($req, $year);

            foreach ($deadlines as $deadline) {
                ComplianceDeadline::firstOrCreate(
                    [
                        'requirement_id' => $req->id,
                        'due_date' => $deadline['date'],
                        'year' => $year,
                    ],
                    [
                        'title' => $deadline['title'],
                        'notes' => $deadline['notes'] ?? null,
                    ]
                );
            }
        }
    }

    private function computeDeadlines(ComplianceRequirement $req, int $year): array
    {
        $deadlines = [];
        $acronym = $req->regulator->acronym;

        switch ($req->frequency) {
            case 'monthly':
                for ($month = 1; $month <= 12; $month++) {
                    // Most regulators: submission due by end of next month (e.g. Jan report due Feb 28)
                    $reportMonth = Carbon::create($year, $month, 1);
                    $due = $reportMonth->copy()->endOfMonth()->addDays(15);
                    if ($due->year !== $year) {
                        continue;
                    }
                    $deadlines[] = [
                        'date' => $due->toDateString(),
                        'title' => $req->title . ' – ' . $reportMonth->format('M Y'),
                    ];
                }
                break;

            case 'quarterly':
                $quarters = [
                    ['end' => Carbon::create($year, 3, 31), 'label' => 'Q1'],
                    ['end' => Carbon::create($year, 6, 30), 'label' => 'Q2'],
                    ['end' => Carbon::create($year, 9, 30), 'label' => 'Q3'],
                    ['end' => Carbon::create($year, 12, 31), 'label' => 'Q4'],
                ];
                foreach ($quarters as $q) {
                    // Typically due 30–45 days after quarter-end
                    $due = $q['end']->copy()->addDays(30);
                    $deadlines[] = [
                        'date' => $due->toDateString(),
                        'title' => $req->title . ' – ' . $q['label'] . ' ' . $year,
                    ];
                }
                break;

            case 'semi_annual':
                $periods = [
                    ['end' => Carbon::create($year, 6, 30), 'label' => 'H1'],
                    ['end' => Carbon::create($year, 12, 31), 'label' => 'H2'],
                ];
                foreach ($periods as $p) {
                    $due = $p['end']->copy()->addDays(45);
                    if ($due->year > $year) {
                        $due = Carbon::create($year + 1, 1, 31);
                    }
                    $deadlines[] = [
                        'date' => $due->toDateString(),
                        'title' => $req->title . ' – ' . $p['label'] . ' ' . $year,
                    ];
                }
                break;

            case 'annual':
                // Most annual filings: due by 30 April following the calendar year-end
                $due = $this->annualDueDate($req, $year);
                $deadlines[] = [
                    'date' => $due->toDateString(),
                    'title' => $req->title . ' – ' . $year,
                    'notes' => 'For financial year ended 31 December ' . $year,
                ];
                break;
        }

        return $deadlines;
    }

    private function annualDueDate(ComplianceRequirement $req, int $year): Carbon
    {
        $acronym = $req->regulator->acronym;

        // Licence renewals: typically due January 1 of next year (renew before lapse)
        if ($req->category === 'Licensing' || $req->category === 'Training') {
            return Carbon::create($year, 12, 31);
        }

        // DFSA / FSRA: 4 months after year-end = April 30
        if (in_array($acronym, ['DFSA', 'FSRA'])) {
            return Carbon::create($year + 1, 4, 30);
        }

        // CBUAE: audited accounts 4 months after year-end = April 30
        if ($acronym === 'CBUAE') {
            return Carbon::create($year + 1, 4, 30);
        }

        // SCA: 3 months after year-end = March 31
        if ($acronym === 'SCA') {
            return Carbon::create($year + 1, 3, 31);
        }

        // RERA / DLD default: March 31
        return Carbon::create($year + 1, 3, 31);
    }
}
