<?php
// app/Support/ComplianceGuide.php
// Content for the public Compliance Guide (/guide). Plain-English, UAE-specific.
// Facts last checked October 2026 — keep 'reviewed' in sync when you edit.

namespace App\Support;

class ComplianceGuide
{
    public const REVIEWED = 'October 2026';

    /** Sector tabs used on every topic page. */
    public const SECTORS = [
        'gold'        => ['icon' => '💎', 'label' => 'Gold & precious stones'],
        'real_estate' => ['icon' => '🏠', 'label' => 'Real estate'],
        'csp'         => ['icon' => '🏢', 'label' => 'Company services'],
        'accounting'  => ['icon' => '🧾', 'label' => 'Accountants & auditors'],
    ];

    public static function topics(): array
    {
        return [
            'aml-regulations' => [
                'step' => 1, 'icon' => '⚖️', 'minutes' => 3,
                'title' => 'The AML rules',
                'nav' => 'What the law asks of you',
                'oneliner' => 'If you handle other people\'s money, property or companies, UAE law makes you part of the country\'s defence against financial crime.',
                'why' => [
                    ['It\'s the law, not best practice', 'Federal Decree-Law No. 10 of 2025 (in force since 14 October 2025) and its executive regulations, Cabinet Resolution No. 134 of 2025, put legal duties on banks and on "DNFBPs" — designated non-financial businesses and professions.'],
                    ['Inspectors do visit', 'In the first half of 2025 alone, the Ministry of Economy and Tourism recorded 1,063 violations and issued more than AED 42 million in fines to DNFBPs.'],
                    ['The new law is stricter', 'The 2025 law adds proliferation financing and tax evasion as offences, makes offences easier to prove, and gives supervisors stronger powers.'],
                ],
                'extra' => [
                    'title' => 'Am I a DNFBP?',
                    'intro' => 'You are, if your business does any of these. Your supervisor for AML is usually the Ministry of Economy and Tourism (free-zone businesses may have their own).',
                    'items' => [
                        ['💎', 'Dealers in precious metals & stones', 'Gold, silver, diamonds, jewellery — trading, refining or retail.'],
                        ['🏠', 'Real estate brokers & agents', 'When you are involved in buying or selling property for a client.'],
                        ['🏢', 'Corporate service providers', 'Forming companies, acting as nominee director or shareholder, providing a registered address.'],
                        ['🧾', 'Accountants & auditors', 'Especially when you manage client money or accounts, or set up and run companies for clients.'],
                        ['⚖️', 'Lawyers & notaries', 'When you prepare or carry out property, company or financial transactions for clients.'],
                    ],
                ],
                'steps' => [
                    ['Register on goAML', 'Register your business with the UAE Financial Intelligence Unit on goAML, its reporting system.'],
                    ['Appoint a compliance officer', 'Someone senior enough to make decisions, who knows the rules and owns the programme.'],
                    ['Assess your risk & write your policies', 'A documented risk assessment, and AML policies that match how you really work.'],
                    ['Know and screen your customers', 'Due diligence and sanctions screening before you deal — and while the relationship lasts.'],
                    ['Report, train, keep records', 'File the reports that apply to you, train your staff, and keep records for at least five years.'],
                ],
                'sectors' => [
                    'gold' => 'All dealers must register on goAML and run an AML programme. Cash deals of AED 55,000 or more also trigger a DPMSR report — so cash-heavy retail counters need tight controls.',
                    'real_estate' => 'Brokers and agents are covered whenever they help a client buy or sell property. Cash payments over AED 55,000, or payments in crypto, must be reported to the FIU.',
                    'csp' => 'Forming companies, providing nominee services or a registered address all bring you in scope — and the companies you create are exactly what launderers look for.',
                    'accounting' => 'Accountants and auditors are covered for client work that involves money, accounts or company structures. Your own professional rules don\'t replace the AML ones.',
                ],
                'mistakes' => [
                    ['"We\'re too small for this."', 'The duties attach to what you do, not how big you are. One-person brokerages get inspected too.'],
                    ['Registering on goAML and stopping there', 'Registration is step one. Inspectors check the whole programme behind it.'],
                    ['A downloaded policy nobody follows', 'A template that doesn\'t match your business is easy for an inspector to spot.'],
                ],
                'help' => 'We set up AML programmes for DNFBPs end to end — goAML registration, policies, risk assessment and staff training — and keep them current as the rules change.',
                'links' => [['Our compliance services', 'services'], ['Compliance calendar', 'compliance-calendar']],
            ],

            'risk-assessment' => [
                'step' => 2, 'icon' => '🎯', 'minutes' => 3,
                'title' => 'Risk assessment',
                'nav' => 'Know where you\'re exposed',
                'oneliner' => 'Work out where financial crime could hit your business — then spend your effort there, not everywhere.',
                'why' => [
                    ['Everything else hangs off it', 'Your risk assessment decides which customers get extra checks, how often you re-screen, and what you train staff on.'],
                    ['It\'s one of the first things inspectors ask for', 'If it\'s missing, generic or out of date, the rest of your programme is hard to defend.'],
                    ['It protects your time', 'A good assessment lets you go lighter on genuinely low-risk customers instead of treating everyone the same.'],
                ],
                'extra' => [
                    'title' => 'Two levels, one idea',
                    'intro' => null,
                    'items' => [
                        ['🏢', 'Business-wide risk assessment', 'How exposed is your business overall? Look at your customers, products, countries, payment methods and how you deal with clients (walk-in, online, through agents). Document it and have senior management sign it off.'],
                        ['👤', 'Customer risk rating', 'Each customer gets a rating — low, medium or high — which sets how much due diligence you do and how often you review them.'],
                    ],
                ],
                'steps' => [
                    ['List what you do and who you serve', 'Your products, services, customer types, countries and payment methods.'],
                    ['Score the risk factors', 'Cash, high-risk countries, complex ownership, PEPs and unusual payment routes all push risk up.'],
                    ['Note the controls you have', 'ID checks, screening, approval limits, staff training — what reduces each risk.'],
                    ['Rate what\'s left and sign it off', 'The remaining ("residual") risk, approved by senior management.'],
                    ['Review it', 'At least once a year is good practice — and whenever your business, customers or the rules change.'],
                ],
                'sectors' => [
                    'gold' => 'Cash-heavy walk-in sales, bullion exports, scrap gold of unclear origin and suppliers from conflict-affected and high-risk areas are your big risk drivers.',
                    'real_estate' => 'Watch for company buyers with unclear owners, cash or crypto payments, buyers from high-risk countries, and quick buy-and-resell deals.',
                    'csp' => 'Layered ownership, nominee arrangements, offshore jurisdictions and clients who want a structure but can\'t explain why.',
                    'accounting' => 'Clients with cash-intensive businesses, unexplained wealth, or where you handle client money directly.',
                ],
                'mistakes' => [
                    ['Using a template as-is', 'Your assessment must describe your business — not a generic one.'],
                    ['Doing it once', 'A risk assessment from three years ago doesn\'t reflect today\'s customers or today\'s law.'],
                    ['Rating everyone "low"', 'If no customer is ever high risk, the rating isn\'t doing its job.'],
                ],
                'help' => 'We write your business-wide risk assessment with you. Our KYC portal then rates each client and schedules their periodic reviews, so ratings don\'t go stale.',
                'links' => [['Our compliance services', 'services'], ['The KYC portal', 'technology']],
            ],

            'due-diligence' => [
                'step' => 3, 'icon' => '🪪', 'minutes' => 3,
                'title' => 'Customer due diligence',
                'nav' => 'Know who you\'re dealing with',
                'oneliner' => 'Know who your customer really is, where their money comes from, and whether the deal makes sense.',
                'why' => [
                    ['Criminals need businesses like yours', 'Property, gold and companies are classic ways to clean money. Proper checks make you a hard target.'],
                    ['Your file is your defence', 'If a customer turns out to be a criminal, what protects you is a record showing you did the right checks.'],
                    ['No checks, no deal', 'If you can\'t complete due diligence, you shouldn\'t go ahead — and you should consider whether to report it.'],
                ],
                'extra' => [
                    'title' => 'Three levels',
                    'intro' => 'How deep you go depends on the customer\'s risk rating.',
                    'items' => [
                        ['🟢', 'Simplified', 'For customers your risk assessment shows are genuinely low risk. Lighter checks — never none.'],
                        ['🔵', 'Standard (CDD)', 'The default for everyone else: identify, verify, understand the purpose, check the source of funds.'],
                        ['🔴', 'Enhanced (EDD)', 'For high risk: politically exposed persons (PEPs), high-risk countries, unusual deals. More evidence, source of wealth, senior approval.'],
                    ],
                ],
                'steps' => [
                    ['Identify and verify', 'Emirates ID or passport for individuals; trade licence and constitutional documents for companies.'],
                    ['Find the real owner', 'For companies, identify the people who ultimately own or control it — typically anyone with 25% or more.'],
                    ['Understand the purpose', 'What is this deal for, and does it fit what you know about the customer?'],
                    ['Check where the money comes from', 'Source of funds for the transaction; source of wealth too for high-risk customers.'],
                    ['Screen, record, keep it current', 'Screen everyone involved, keep the file for at least five years, and update it when things change.'],
                ],
                'sectors' => [
                    'gold' => 'A walk-in customer wants to buy a kilo bar in cash. You need their ID, why they\'re buying, and where the cash came from — and at AED 55,000 or more in cash, a DPMSR.',
                    'real_estate' => 'The buyer is a company. Get the trade licence and shareholder details, identify who\'s really behind it, and see evidence of where the deposit is coming from.',
                    'csp' => 'Before forming a company, identify every shareholder and ultimate owner, and understand what the company is actually for.',
                    'accounting' => 'For a new client: who owns them, what the business really does, and whether the numbers you\'ll be working with make sense for it.',
                ],
                'mistakes' => [
                    ['A passport copy is not KYC', 'Identity is one step of five. Purpose and source of funds matter just as much.'],
                    ['Stopping at the company', 'The risk is in the people behind it. Always get to the ultimate owners.'],
                    ['Files that never get updated', 'An expired ID and a five-year-old profile won\'t satisfy an inspector.'],
                ],
                'help' => 'Our KYC portal sends customers a secure self-fill link, scans their documents to pull out names, dates and ID numbers, and follows a checklist built for your sector.',
                'links' => [['The KYC portal', 'technology'], ['Our compliance services', 'services']],
            ],

            'screening' => [
                'step' => 4, 'icon' => '🔍', 'minutes' => 3,
                'title' => 'Sanctions screening',
                'nav' => 'Check them against the lists',
                'oneliner' => 'Check every customer — and the people behind them — against sanctions and watch lists, before you deal and for as long as you deal.',
                'why' => [
                    ['24 hours to freeze', 'If a customer is on the UAE Local Terrorist List or the UN Consolidated List, you must freeze their funds without delay — within 24 hours of the listing.'],
                    ['5 business days to report', 'Confirmed and partial matches must be reported to the authorities through goAML within five business days.'],
                    ['Lists change constantly', 'A customer who was clear at onboarding can be listed next month. One-off screening isn\'t enough.'],
                ],
                'extra' => [
                    'title' => 'What to screen against',
                    'intro' => null,
                    'items' => [
                        ['🇦🇪', 'UAE Local Terrorist List & UN Consolidated List', 'Mandatory. These are the lists the UAE\'s freeze-without-delay rules are built on.'],
                        ['🏛️', 'Politically exposed persons (PEPs)', 'Not banned — but a PEP match means enhanced due diligence and senior sign-off.'],
                        ['🌍', 'Other sanctions lists', 'OFAC, EU and UK lists matter if your customers, banks or deals touch those jurisdictions.'],
                        ['📰', 'Adverse media', 'News of fraud, corruption or financial crime is a red flag even when no list mentions them.'],
                    ],
                ],
                'steps' => [
                    ['Get the list updates', 'Register on the Executive Office (EOCN) notification system so you hear about new listings.'],
                    ['Screen at onboarding', 'The customer, plus owners, directors and anyone authorised to act for them.'],
                    ['Re-screen when lists change', 'Your existing customers, not just new ones.'],
                    ['Handle a match properly', 'Confirmed: freeze within 24 hours and file an FFR. Partial: suspend and file a PNMR. Both within five business days.'],
                    ['Document false positives', 'If you clear a hit, write down why. "Different date of birth" beats an unexplained tick.'],
                ],
                'sectors' => [
                    'gold' => 'Screen suppliers and refiners as well as buyers — sanctioned gold often enters through the supply chain, not the shop counter.',
                    'real_estate' => 'Screen buyer and seller, the companies behind them, and anyone paying on their behalf.',
                    'csp' => 'Every shareholder, director, ultimate owner and nominee — and again whenever ownership changes.',
                    'accounting' => 'Your clients and their owners, and re-screen your client list when the lists are updated.',
                ],
                'mistakes' => [
                    ['Screening the company, not the people', 'Sanctioned individuals hide behind companies. Screen the owners and directors too.'],
                    ['Screening once', 'If you\'re not re-screening when lists change, you can\'t freeze "without delay".'],
                    ['Clearing hits with no reason recorded', 'An inspector will ask why each hit was dismissed.'],
                ],
                'help' => 'Sentinel, our screening software, checks sanctions, PEP, law-enforcement and adverse-media sources in one search, re-screens your customers automatically, and keeps the case record for you.',
                'links' => [['Sentinel AML screening', 'sentinel'], ['The KYC portal', 'technology']],
            ],

            'goaml' => [
                'step' => 5, 'icon' => '📨', 'minutes' => 3,
                'title' => 'goAML reporting',
                'nav' => 'Tell the authorities',
                'oneliner' => 'goAML is the FIU\'s online system. You register on it, and every AML report you\'re required to make goes through it.',
                'why' => [
                    ['Registration is mandatory', 'The Ministry of Economy has suspended DNFBPs for three months for failing to register on goAML.'],
                    ['Reports have deadlines', 'Some reports are due "without delay", others within days. Late is a breach.'],
                    ['Never tip off', 'Telling a customer you\'ve reported them, or are about to, is an offence in its own right.'],
                ],
                'extra' => [
                    'title' => 'The reports you\'re most likely to file',
                    'intro' => null,
                    'items' => [
                        ['STR / SAR', 'Suspicious transaction or activity', 'Whenever you suspect money laundering or terrorist financing — including attempts. File without delay.'],
                        ['DPMSR', 'Dealers in precious metals & stones', 'Cash transactions of AED 55,000 or more.'],
                        ['REAR', 'Real estate activity', 'Property deals with cash payments over AED 55,000, or paid with crypto or funds from crypto.'],
                        ['FFR', 'Funds freeze', 'You found a confirmed sanctions match and froze their funds. Within five business days.'],
                        ['PNMR', 'Partial name match', 'A possible sanctions match you couldn\'t rule out. Within five business days.'],
                    ],
                    'table' => true,
                ],
                'steps' => [
                    ['Register on goAML', 'Your business and your compliance officer, with the UAE FIU.'],
                    ['Know which reports apply', 'Your sector decides most of it — see the table above.'],
                    ['Set up an internal route', 'Staff raise concerns with the compliance officer, who decides whether to report — and records the decision either way.'],
                    ['File on time, say why', 'A good report explains what made you suspicious, not just what happened.'],
                    ['Keep it confidential', 'Keep a copy and your reasoning on file, and never tell the customer.'],
                ],
                'sectors' => [
                    'gold' => 'DPMSRs are your routine report. On top of that: STRs/SARs for suspicion, and FFRs/PNMRs if screening finds a match.',
                    'real_estate' => 'REARs for cash and crypto-funded deals, STRs/SARs for suspicion, and FFRs/PNMRs for sanctions matches.',
                    'csp' => 'Mostly STRs/SARs — for example when a client can\'t explain a structure — plus FFRs/PNMRs for sanctions matches.',
                    'accounting' => 'STRs/SARs when client activity doesn\'t add up, and FFRs/PNMRs for sanctions matches.',
                ],
                'mistakes' => [
                    ['Never logging in', 'The FIU sends messages and requests through goAML. Unread doesn\'t mean unanswered in their eyes.'],
                    ['Reporting late', '"We were busy" isn\'t a defence for a missed deadline.'],
                    ['Reports that don\'t explain', 'Describe what made you suspicious. A bare list of transactions helps no one.'],
                ],
                'help' => 'We register you on goAML and can prepare and file reports with you. Our KYC portal generates goAML-ready STR, SAR and DPMSR files from your client records.',
                'links' => [['Our compliance services', 'services'], ['The KYC portal', 'technology']],
            ],
        ];
    }

    public static function topic(string $slug): ?array
    {
        $t = self::topics()[$slug] ?? null;
        return $t ? ['slug' => $slug] + $t : null;
    }

    /** [previous, next] topic (or null) around $slug, in step order. */
    public static function neighbours(string $slug): array
    {
        $slugs = array_keys(self::topics());
        $i = array_search($slug, $slugs, true);

        return [
            $i > 0 ? self::topic($slugs[$i - 1]) : null,
            $i < count($slugs) - 1 ? self::topic($slugs[$i + 1]) : null,
        ];
    }
}
