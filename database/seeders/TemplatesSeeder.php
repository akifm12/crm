<?php
// database/seeders/TemplatesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SlaTemplate;
use App\Models\QuotationTemplate;

class TemplatesSeeder extends Seeder
{
    // ── BA standard header info ─────────────────────────────────────────────
    const BA_NAME    = 'Blue Arrow Management Consultants FZC';
    const BA_ADDRESS = 'B1602, SRTIP Building, Sharjah, UAE';
    const BA_EMAIL   = 'info@bluearrow.ae';
    const BA_TEL     = '050-8474481';
    const BA_LICENSE = 'Blue Arrow Management Consultants FZC is licensed under Sharjah Research Technology & Innovation Park';

    // ── Shared standard clauses ─────────────────────────────────────────────
    const PERIODIC_REVIEW = 'This Agreement is valid from the Effective Date outlined herein and is valid until further notice. This Agreement should be reviewed at a minimum once per fiscal year; however, in lieu of a review during any period specified, the current Agreement will remain in effect. The Business Relationship Manager is responsible for facilitating regular reviews of this document. Contents of this document may be amended as required, provided mutual agreement is obtained from the primary stakeholders. Review Period: Quarterly.';

    const SERVICE_AVAILABILITY = "Coverage parameters specific to the service(s) covered in this Agreement are as follows:\n\n- Telephone support: 9:00 A.M. to 5:00 P.M. Monday – Friday.\n- Email support: Monitored 8:00 A.M. to 6:00 P.M. Monday – Friday.\n- Onsite assistance guaranteed within 72 hours during the business week.";

    const RESPONSE_TIMES = "The Service Provider will respond to service-related incidents and/or requests submitted by the Customer within the following time frames:\n\n- 0–8 hours (during business hours) for issues classified as High priority.\n- Within 24 hours for issues classified as Medium priority.\n- Within 3 working days for issues classified as Low priority.\n- Remote assistance will be provided in-line with the above timescales dependent on the priority of the support request.\n\nAny changes to this SLA will be with express mutual consent of both parties.";

    const CLIENT_OBLIGATIONS = "Customer responsibilities and/or requirements in support of this Agreement include:\n\n- Payment for all support costs at the agreed interval.\n- Provision of any and all data and information as required for regulatory purposes.\n- Availability of internal staff for internal support as and when needed.\n- Timely notification of any changes in business structure, ownership, or operations that may impact compliance obligations.";

    const TERMINATION = "Either party may terminate this Agreement by providing thirty (30) calendar days' written notice to the other party. Blue Arrow Management Consultants FZC reserves the right to terminate this Agreement immediately in the event of non-payment or material breach of its terms. Upon termination, all outstanding fees shall become immediately due and payable.";

    const GOVERNING_LAW = 'This Agreement shall be governed by and construed in accordance with the laws of the United Arab Emirates. Any disputes arising under this Agreement shall be subject to the exclusive jurisdiction of the courts of the Emirate of Sharjah, UAE.';

    const PAYMENT_TERMS = 'Fees are payable within 14 calendar days of invoice date. Late payments shall attract interest at the rate of 2% per month on the outstanding balance. Blue Arrow Management Consultants FZC reserves the right to suspend services in the event of non-payment beyond 30 days.';

    public function run(): void
    {
        // ── SLA TEMPLATES ───────────────────────────────────────────────────

        $slaTemplates = [

            // 1. Full Scale Compliance Services
            [
                'name'         => 'Full Scale Compliance Services',
                'service_type' => 'Compliance & AML Support',
                'description'  => 'Comprehensive AML/CFT compliance support including regulatory advisory, operational manuals, employee training, and screening assistance.',
                'scope_of_work' =>
                    "The following services are covered by this Agreement:\n\n" .
                    "- Compliance and Regulatory Support & Advisory.\n" .
                    "- Assistance in drafting and maintaining Operational and Compliance Manuals.\n" .
                    "- Full support in off-site and on-site Regulatory inspections.\n" .
                    "- Employee Training Sessions in AML and CFT methodology.\n" .
                    "- AML/CFT risk assessment reviews and updates.\n" .
                    "- Customer Due Diligence (CDD) and Enhanced Due Diligence (EDD) support.\n" .
                    "- Sanctions screening and watchlist monitoring support.\n" .
                    "- goAML filing assistance and Suspicious Transaction Report (STR) advisory.\n" .
                    "- Manned telephone support.\n" .
                    "- Monitored email support.\n" .
                    "- Remote assistance using Remote Desktop and a Virtual Private Network where available.\n" .
                    "- Monthly compliance health check.",
                'client_obligations' => self::CLIENT_OBLIGATIONS,
                'deliverables'  =>
                    "- Monthly compliance status report.\n" .
                    "- Updated risk assessment (annually or upon material change).\n" .
                    "- Training completion certificates for all employees trained.\n" .
                    "- Written advisory opinions on regulatory matters as requested.\n" .
                    "- Support documentation for regulatory inspections.",
                'duration'      => '12 months',
                'default_fee'   => 0,
                'fee_frequency' => 'monthly',
                'payment_terms' => self::PAYMENT_TERMS,
                'termination_clause' => self::TERMINATION,
                'governing_law' => self::GOVERNING_LAW,
            ],

            // 2. AML Consulting Retainer
            [
                'name'         => 'AML Consulting Retainer',
                'service_type' => 'AML Consulting',
                'description'  => 'Ongoing AML/CFT consulting retainer for DNFBPs — advisory, policy review, and regulatory support on a retainer basis.',
                'scope_of_work' =>
                    "The following services are covered under this retainer Agreement:\n\n" .
                    "- Ongoing AML/CFT regulatory advisory and guidance.\n" .
                    "- Review and update of AML/CFT policies and procedures.\n" .
                    "- Advisory on Customer Due Diligence (CDD) and risk classification.\n" .
                    "- Guidance on Politically Exposed Person (PEP) and sanctions screening.\n" .
                    "- Support with UAE Financial Intelligence Unit (goAML) reporting obligations.\n" .
                    "- Advisory on Targeted Financial Sanctions (TFS) compliance.\n" .
                    "- Regulatory update notifications relevant to the client's business sector.\n" .
                    "- Email and telephone advisory support within agreed response times.",
                'client_obligations' => self::CLIENT_OBLIGATIONS,
                'deliverables'  =>
                    "- Quarterly compliance advisory summary.\n" .
                    "- Regulatory update briefings as and when issued by UAE authorities.\n" .
                    "- Written advisory responses to compliance queries within agreed response times.",
                'duration'      => '12 months',
                'default_fee'   => 0,
                'fee_frequency' => 'monthly',
                'payment_terms' => self::PAYMENT_TERMS,
                'termination_clause' => self::TERMINATION,
                'governing_law' => self::GOVERNING_LAW,
            ],

            // 3. Employee Training Programme
            [
                'name'         => 'Employee Training Programme',
                'service_type' => 'Employee Training',
                'description'  => 'AML/CFT employee training sessions — delivered on-site or remotely — covering UAE regulatory requirements, red flags, and compliance procedures.',
                'scope_of_work' =>
                    "The following training services are covered by this Agreement:\n\n" .
                    "- AML/CFT awareness and fundamentals training for all staff.\n" .
                    "- Role-specific training for Compliance Officers and senior management.\n" .
                    "- Training on UAE Federal Decree-Law No. 20 of 2018 and its Executive Regulation.\n" .
                    "- Sanctions compliance and Targeted Financial Sanctions (TFS) training.\n" .
                    "- Customer Due Diligence (CDD) and Know Your Customer (KYC) procedures training.\n" .
                    "- Red flags identification and Suspicious Transaction Reporting (STR) training.\n" .
                    "- goAML portal usage and reporting obligations.\n" .
                    "- Training may be delivered on-site at the client's premises or remotely via video conference.\n" .
                    "- Training materials and handouts will be provided in digital format.",
                'client_obligations' =>
                    "Customer responsibilities and/or requirements in support of this Agreement include:\n\n" .
                    "- Provision of a suitable venue and AV equipment for on-site training sessions.\n" .
                    "- Ensuring attendance of all required staff at scheduled training sessions.\n" .
                    "- Providing a list of attendees and their roles prior to each session.\n" .
                    "- Payment for all training costs at the agreed interval.\n" .
                    "- Timely communication of any scheduling changes with at least 48 hours' notice.",
                'deliverables'  =>
                    "- Pre-training needs assessment.\n" .
                    "- Training delivery (on-site or remote) as per agreed schedule.\n" .
                    "- Training materials in digital format for each attendee.\n" .
                    "- Post-training assessment and knowledge check.\n" .
                    "- Training completion certificates for all attendees.\n" .
                    "- Post-training report summarising attendance and outcomes.",
                'duration'      => 'Per engagement / as agreed',
                'default_fee'   => 0,
                'fee_frequency' => 'per session',
                'payment_terms' => self::PAYMENT_TERMS,
                'termination_clause' => self::TERMINATION,
                'governing_law' => self::GOVERNING_LAW,
            ],

            // 4. Policy Manuals Creation
            [
                'name'         => 'Policy & Procedures Manual Creation',
                'service_type' => 'Policy Manuals',
                'description'  => 'Creation or update of AML/CFT policies and procedures manuals, compliance frameworks, and internal control documentation.',
                'scope_of_work' =>
                    "The following services are covered by this Agreement:\n\n" .
                    "- Gap analysis of existing policies and procedures against UAE AML/CFT regulatory requirements.\n" .
                    "- Drafting of a comprehensive AML/CFT Policy and Procedures Manual tailored to the client's business sector and risk profile.\n" .
                    "- Drafting of a Customer Acceptance Policy (CAP) and Customer Risk Classification Framework.\n" .
                    "- Drafting of a Suspicious Transaction Reporting (STR) Policy and internal escalation procedures.\n" .
                    "- Drafting of a Sanctions Compliance Policy covering UAE, UN, OFAC, EU, and UK sanctions regimes.\n" .
                    "- Drafting of a Record Keeping and Data Retention Policy.\n" .
                    "- Review and finalisation in consultation with client's management.\n" .
                    "- Delivery of final documents in editable Microsoft Word format and PDF.",
                'client_obligations' =>
                    "Customer responsibilities and/or requirements in support of this Agreement include:\n\n" .
                    "- Provision of existing policy documents, business overview, and organisational structure.\n" .
                    "- Availability of key staff (Compliance Officer, senior management) for consultation sessions.\n" .
                    "- Timely review and feedback on draft documents within agreed timeframes.\n" .
                    "- Payment for services at the agreed interval.",
                'deliverables'  =>
                    "- Gap analysis report.\n" .
                    "- Draft AML/CFT Policy and Procedures Manual for client review.\n" .
                    "- Final AML/CFT Policy and Procedures Manual (Word and PDF).\n" .
                    "- Customer Acceptance Policy (CAP).\n" .
                    "- Sanctions Compliance Policy.\n" .
                    "- Record Keeping Policy.\n" .
                    "- Up to two rounds of revisions included.",
                'duration'      => 'Per project (4–8 weeks)',
                'default_fee'   => 0,
                'fee_frequency' => 'fixed',
                'payment_terms' => "50% upon engagement, 50% upon delivery of final documents.\n\n" . self::PAYMENT_TERMS,
                'termination_clause' => self::TERMINATION,
                'governing_law' => self::GOVERNING_LAW,
            ],

            // 5. Screening Software Services
            [
                'name'         => 'Screening Software Service',
                'service_type' => 'Screening Software',
                'description'  => 'Access to Blue Arrow\'s AML/sanctions screening software platform — covering sanctions, PEP, adverse media, and watchlist screening.',
                'scope_of_work' =>
                    "The following services are covered by this Agreement:\n\n" .
                    "- Access to Blue Arrow's AML/CFT screening software platform.\n" .
                    "- Sanctions screening against UN, OFAC, EU, HMT, and UAE Cabinet Decision No. 74 lists.\n" .
                    "- Politically Exposed Person (PEP) screening.\n" .
                    "- Adverse media screening.\n" .
                    "- Ongoing monitoring and re-screening alerts.\n" .
                    "- Batch upload capability for screening multiple subjects.\n" .
                    "- Access to screening results, match reports, and audit trail.\n" .
                    "- System onboarding and user training.\n" .
                    "- Technical support during business hours.",
                'client_obligations' =>
                    "Customer responsibilities and/or requirements in support of this Agreement include:\n\n" .
                    "- Use of the platform in accordance with applicable UAE AML/CFT laws and regulations.\n" .
                    "- Ensuring authorised users only are granted access to the platform.\n" .
                    "- Immediate notification to Blue Arrow of any suspected data breach or unauthorised access.\n" .
                    "- Payment of subscription fees at the agreed interval.\n" .
                    "- Compliance with the platform's acceptable use policy.",
                'deliverables'  =>
                    "- Platform access credentials and onboarding.\n" .
                    "- User training session.\n" .
                    "- Access to screening results and downloadable reports.\n" .
                    "- Monthly platform uptime guarantee of 99.5%.\n" .
                    "- Technical support response within 4 business hours for critical issues.",
                'duration'      => '12 months (auto-renewable)',
                'default_fee'   => 0,
                'fee_frequency' => 'monthly',
                'payment_terms' => self::PAYMENT_TERMS,
                'termination_clause' => self::TERMINATION,
                'governing_law' => self::GOVERNING_LAW,
            ],

            // 6. KYC Software / Portal
            [
                'name'         => 'KYC Management Portal',
                'service_type' => 'KYC Software',
                'description'  => 'Access to Blue Arrow\'s KYC management portal for onboarding, document management, risk assessment, and ongoing client monitoring.',
                'scope_of_work' =>
                    "The following services are covered by this Agreement:\n\n" .
                    "- Access to Blue Arrow's KYC Management Portal (hosted at bluearrow.ae).\n" .
                    "- Client onboarding workflow — individual and corporate KYC forms.\n" .
                    "- Document upload and secure storage with expiry tracking.\n" .
                    "- Customer risk rating and CDD/EDD workflow.\n" .
                    "- KYC review scheduling and alerts for document expiry and review dates.\n" .
                    "- Integrated sanctions and PEP screening (where subscribed).\n" .
                    "- goAML report generation (STR/SAR XML output for UAE FIU submission).\n" .
                    "- Audit trail and compliance activity log.\n" .
                    "- Staff user management with role-based access control.\n" .
                    "- System onboarding, data migration support, and user training.",
                'client_obligations' =>
                    "Customer responsibilities and/or requirements in support of this Agreement include:\n\n" .
                    "- Use of the portal strictly for lawful compliance purposes in accordance with UAE AML/CFT law.\n" .
                    "- Ensuring that only authorised staff are granted user access.\n" .
                    "- Maintaining the accuracy and completeness of data entered into the portal.\n" .
                    "- Immediate notification to Blue Arrow of any suspected data breach or security incident.\n" .
                    "- Payment of subscription fees at the agreed interval.",
                'deliverables'  =>
                    "- Portal access and user account setup.\n" .
                    "- Onboarding and data migration support.\n" .
                    "- User training session (up to 2 hours).\n" .
                    "- Ongoing technical support during business hours.\n" .
                    "- Platform uptime guarantee of 99.5% per month.\n" .
                    "- Monthly platform update notifications.",
                'duration'      => '12 months (auto-renewable)',
                'default_fee'   => 0,
                'fee_frequency' => 'monthly',
                'payment_terms' => self::PAYMENT_TERMS,
                'termination_clause' => self::TERMINATION,
                'governing_law' => self::GOVERNING_LAW,
            ],

            // 7. Regulatory Inspection Support
            [
                'name'         => 'Regulatory Inspection Support',
                'service_type' => 'Inspection Support',
                'description'  => 'Dedicated support for regulatory inspections — preparation, on-site attendance, and post-inspection remediation.',
                'scope_of_work' =>
                    "The following services are covered by this Agreement:\n\n" .
                    "- Pre-inspection readiness review and gap analysis.\n" .
                    "- Preparation of inspection files, documentation packs, and evidence bundles.\n" .
                    "- Briefing of client staff on inspection procedures and expectations.\n" .
                    "- On-site attendance during regulatory inspection (where permitted by regulator).\n" .
                    "- Liaison with the regulating authority on behalf of the client as required.\n" .
                    "- Post-inspection report review and response preparation.\n" .
                    "- Remediation plan development and implementation support.",
                'client_obligations' =>
                    "Customer responsibilities and/or requirements in support of this Agreement include:\n\n" .
                    "- Immediate notification to Blue Arrow upon receipt of any inspection notice.\n" .
                    "- Full cooperation and provision of all required documentation.\n" .
                    "- Ensuring availability of key staff during the inspection period.\n" .
                    "- Payment for services at the agreed rate.",
                'deliverables'  =>
                    "- Pre-inspection readiness report.\n" .
                    "- Inspection documentation pack.\n" .
                    "- On-site inspection support (as agreed).\n" .
                    "- Post-inspection response letter (draft).\n" .
                    "- Remediation action plan.",
                'duration'      => 'Per engagement',
                'default_fee'   => 0,
                'fee_frequency' => 'fixed',
                'payment_terms' => "50% upon engagement, 50% upon completion.\n\n" . self::PAYMENT_TERMS,
                'termination_clause' => self::TERMINATION,
                'governing_law' => self::GOVERNING_LAW,
            ],
        ];

        foreach ($slaTemplates as $t) {
            SlaTemplate::firstOrCreate(['name' => $t['name']], array_merge($t, [
                'is_active'  => true,
                'created_by' => 1,
            ]));
        }

        // ── QUOTATION TEMPLATES ─────────────────────────────────────────────

        $qtTemplates = [

            [
                'name'         => 'Full Scale Compliance Services',
                'service_type' => 'Compliance & AML Support',
                'description'  => 'Comprehensive compliance retainer including advisory, manuals, training and screening support.',
                'line_items'   => [
                    ['description' => 'Monthly AML/CFT Compliance Retainer', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Regulatory advisory and support (included)', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Monthly compliance health check (included)', 'qty' => 1, 'unit_price' => 0],
                ],
                'terms'        => "1. This quotation is valid for 30 days from the date of issue.\n2. Fees are subject to 5% VAT.\n3. Payment terms: 14 days from invoice date.\n4. Services commence upon receipt of signed SLA and initial payment.",
                'validity_days'=> 30,
            ],

            [
                'name'         => 'Employee Training — AML/CFT',
                'service_type' => 'Employee Training',
                'description'  => 'AML/CFT awareness and compliance training for staff.',
                'line_items'   => [
                    ['description' => 'AML/CFT Awareness Training Session (up to 2 hours)', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Training materials (digital, per attendee)', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Post-training assessment and certificates', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Training report', 'qty' => 1, 'unit_price' => 0],
                ],
                'terms'        => "1. This quotation is valid for 30 days from the date of issue.\n2. Fees are subject to 5% VAT.\n3. Payment terms: 100% in advance of training delivery.\n4. Cancellation with less than 48 hours' notice will incur a 50% cancellation fee.",
                'validity_days'=> 30,
            ],

            [
                'name'         => 'Policy & Procedures Manual',
                'service_type' => 'Policy Manuals',
                'description'  => 'Creation of AML/CFT policy and procedures documentation.',
                'line_items'   => [
                    ['description' => 'Gap analysis and needs assessment', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'AML/CFT Policy & Procedures Manual (draft + 2 revisions)', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Customer Acceptance Policy (CAP)', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Sanctions Compliance Policy', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Record Keeping Policy', 'qty' => 1, 'unit_price' => 0],
                ],
                'terms'        => "1. This quotation is valid for 30 days from the date of issue.\n2. Fees are subject to 5% VAT.\n3. Payment terms: 50% upon engagement, 50% upon delivery.\n4. Project timeline: 4–8 weeks from engagement date.",
                'validity_days'=> 30,
            ],

            [
                'name'         => 'Screening Software Subscription',
                'service_type' => 'Screening Software',
                'description'  => 'Monthly subscription to AML/sanctions screening platform.',
                'line_items'   => [
                    ['description' => 'AML/Sanctions Screening Platform — Monthly Subscription', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Onboarding and user training (one-time)', 'qty' => 1, 'unit_price' => 0],
                ],
                'terms'        => "1. This quotation is valid for 30 days from the date of issue.\n2. Fees are subject to 5% VAT.\n3. Subscription is billed monthly in advance.\n4. Minimum subscription term: 12 months.\n5. Payment terms: 14 days from invoice date.",
                'validity_days'=> 30,
            ],

            [
                'name'         => 'KYC Management Portal Subscription',
                'service_type' => 'KYC Software',
                'description'  => 'Monthly subscription to the Blue Arrow KYC Management Portal.',
                'line_items'   => [
                    ['description' => 'KYC Management Portal — Monthly Subscription', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Onboarding, data migration support, and user training (one-time)', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Technical support (included)', 'qty' => 1, 'unit_price' => 0],
                ],
                'terms'        => "1. This quotation is valid for 30 days from the date of issue.\n2. Fees are subject to 5% VAT.\n3. Subscription is billed monthly in advance.\n4. Minimum subscription term: 12 months.\n5. Payment terms: 14 days from invoice date.",
                'validity_days'=> 30,
            ],

            [
                'name'         => 'Regulatory Inspection Support',
                'service_type' => 'Inspection Support',
                'description'  => 'Support package for regulatory inspection preparation and attendance.',
                'line_items'   => [
                    ['description' => 'Pre-inspection readiness review', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Documentation pack preparation', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'On-site inspection attendance (per day)', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Post-inspection response and remediation plan', 'qty' => 1, 'unit_price' => 0],
                ],
                'terms'        => "1. This quotation is valid for 30 days from the date of issue.\n2. Fees are subject to 5% VAT.\n3. Payment terms: 50% upon engagement, 50% upon completion.\n4. Additional days of on-site attendance will be charged at the agreed day rate.",
                'validity_days'=> 30,
            ],

            [
                'name'         => 'AML Risk Assessment',
                'service_type' => 'Risk Assessment',
                'description'  => 'Business-wide AML/CFT risk assessment in line with UAE regulatory requirements.',
                'line_items'   => [
                    ['description' => 'Business-wide AML/CFT Risk Assessment', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Risk assessment report and recommendations', 'qty' => 1, 'unit_price' => 0],
                    ['description' => 'Risk mitigation action plan', 'qty' => 1, 'unit_price' => 0],
                ],
                'terms'        => "1. This quotation is valid for 30 days from the date of issue.\n2. Fees are subject to 5% VAT.\n3. Payment terms: 100% upon engagement.\n4. Delivery timeline: 2–4 weeks from engagement date.",
                'validity_days'=> 30,
            ],
        ];

        foreach ($qtTemplates as $t) {
            QuotationTemplate::firstOrCreate(['name' => $t['name']], array_merge($t, [
                'is_active'  => true,
                'created_by' => 1,
            ]));
        }

        $this->command->info('✓ SLA templates seeded: ' . count($slaTemplates));
        $this->command->info('✓ Quotation templates seeded: ' . count($qtTemplates));
    }
}
