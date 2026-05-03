<?php
// app/Support/SectorConfig.php

namespace App\Support;

class SectorConfig
{
    // ── Sector definitions ─────────────────────────────────────────────────

    public static function sectors(): array
    {
        return [
            'gold'             => 'Precious Metals & Stones (Gold)',
            'real_estate'      => 'Real Estate Brokers & Agents',
            'company_services' => 'Company Service Providers (CSPs)',
            'accounting'       => 'Accountants & Auditors',
            'other'            => 'Other DNFBP',
        ];
    }

    // ── Get full config for a sector ──────────────────────────────────────

    public static function get(string $sector): array
    {
        return match($sector) {
            'gold'             => static::gold(),
            'real_estate'      => static::realEstate(),
            'company_services' => static::companyServices(),
            'accounting'       => static::accounting(),
            default            => static::other(),
        };
    }

    // ── GOLD ───────────────────────────────────────────────────────────────

    private static function gold(): array
    {
        return [
            'label'        => 'Precious Metals & Stones',
            'client_types' => [
                'corporate_local'  => 'Corporate — Local',
                'corporate_import' => 'Corporate — Import',
                'corporate_export' => 'Corporate — Export',
                'individual'       => 'Individual',
            ],
            'show_supply_chain'   => true,
            'show_cahra'          => true,
            'show_expected_volume'=> true,
            'goaml_threshold'     => 55000,
            'goaml_reason'        => 'Sale Above AED 55000',
            'source_of_funds'     => [
                'trading_revenue' => 'Gold / bullion trading revenue',
                'salary'          => 'Salary / employment income',
                'business_income' => 'Business income',
                'investment'      => 'Investment income',
                'inheritance'     => 'Inheritance',
                'asset_sale'      => 'Sale of assets',
                'bank_finance'    => 'Bank / trade finance',
                'other'           => 'Other',
            ],
            'source_of_wealth'    => [
                'uae_business'    => 'UAE business operations',
                'foreign_business'=> 'Foreign business operations',
                'salary'          => 'Salary / employment',
                'real_estate'     => 'Real estate',
                'inheritance'     => 'Inheritance',
                'savings'         => 'Savings',
                'investment'      => 'Investment returns',
                'other'           => 'Other',
            ],
            'declarations_corporate' => ['pep','supply_chain','cahra','source_of_funds','sanctions','ubo'],
            'declarations_individual'=> ['pep','source_of_funds','sanctions','cahra'],
            'risk_categories'     => ['customer','geographic','product','transaction','channel','supply_chain'],
            'corporate_doc_types' => [
                ['type'=>'trade_license',      'label'=>'Trade licence',                'has_expiry'=>true],
                ['type'=>'ejari',              'label'=>'Ejari contract',               'has_expiry'=>true],
                ['type'=>'moa',                'label'=>'Memorandum of Association',    'has_expiry'=>false],
                ['type'=>'passport',           'label'=>'Passport (signatory)',         'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID (signatory)',      'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
            'individual_doc_types' => [
                ['type'=>'passport',           'label'=>'Passport',                     'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID',                  'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
        ];
    }

    // ── REAL ESTATE ────────────────────────────────────────────────────────

    private static function realEstate(): array
    {
        return [
            'label'        => 'Real Estate',
            'client_types' => [
                'corporate' => 'Corporate',
                'individual' => 'Individual',
            ],
            'show_ejari'          => false,
            'show_supply_chain'   => false,
            'show_cahra'          => false,
            'show_expected_volume'=> false,
            'goaml_threshold'     => 55000,
            'goaml_reason'        => 'Real Estate Transaction Above AED 55000',
            'source_of_funds'     => [
                'salary'          => 'Salary / employment income',
                'business_income' => 'Business income',
                'investment'      => 'Investment returns',
                'property_sale'   => 'Proceeds from property sale',
                'mortgage'        => 'Mortgage / bank financing',
                'inheritance'     => 'Inheritance',
                'savings'         => 'Savings',
                'other'           => 'Other',
            ],
            'source_of_wealth'    => [
                'uae_business'    => 'UAE business operations',
                'foreign_business'=> 'Foreign business operations',
                'salary'          => 'Salary / employment',
                'real_estate'     => 'Real estate portfolio',
                'inheritance'     => 'Inheritance',
                'savings'         => 'Savings',
                'investment'      => 'Investment returns',
                'other'           => 'Other',
            ],
            'declarations_corporate' => ['pep','source_of_funds','sanctions','ubo','property'],
            'declarations_individual'=> ['pep','source_of_funds','sanctions','property'],
            'risk_categories'     => ['customer','geographic','property','transaction','channel'],
            'extra_fields'        => [
                'property_type'        => ['label'=>'Property type', 'type'=>'select', 'options'=>['residential'=>'Residential','commercial'=>'Commercial','mixed'=>'Mixed use','land'=>'Land / plot','industrial'=>'Industrial']],
                'property_location'    => ['label'=>'Property location / area', 'type'=>'text'],
                'transaction_purpose'  => ['label'=>'Purpose of transaction', 'type'=>'select', 'options'=>['own_use'=>'Own use / occupancy','investment'=>'Investment','resale'=>'Resale','development'=>'Development']],
                'property_value'       => ['label'=>'Property value (AED)', 'type'=>'number'],
                'rera_number'          => ['label'=>'RERA registration number', 'type'=>'text'],
            ],
            'corporate_doc_types' => [
                ['type'=>'trade_license',      'label'=>'Trade licence',                'has_expiry'=>true],
                ['type'=>'moa',                'label'=>'Memorandum of Association',    'has_expiry'=>false],
                ['type'=>'passport',           'label'=>'Passport (signatory)',         'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID (signatory)',      'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'title_deed',         'label'=>'Title deed',                  'has_expiry'=>false],
                ['type'=>'noc',                'label'=>'NOC from developer',           'has_expiry'=>true],
                ['type'=>'spa',                'label'=>'Sale & purchase agreement',    'has_expiry'=>false],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
            'individual_doc_types' => [
                ['type'=>'passport',           'label'=>'Passport',                     'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID',                  'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'title_deed',         'label'=>'Title deed',                  'has_expiry'=>false],
                ['type'=>'noc',                'label'=>'NOC from developer',           'has_expiry'=>true],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
        ];
    }

    // ── COMPANY SERVICE PROVIDERS ──────────────────────────────────────────

    private static function companyServices(): array
    {
        return [
            'label'        => 'Company Service Providers',
            'client_types' => [
                'corporate_local'  => 'Corporate — Local',
                'corporate_foreign'=> 'Corporate — Foreign',
                'individual'       => 'Individual',
            ],
            'show_ejari'          => false,
            'show_supply_chain'   => false,
            'show_cahra'          => false,
            'show_expected_volume'=> true,
            'goaml_threshold'     => 55000,
            'goaml_reason'        => 'Transaction Above AED 55000',
            'source_of_funds'     => [
                'business_income' => 'Business income',
                'salary'          => 'Salary / employment income',
                'investment'      => 'Investment returns',
                'shareholder_loan'=> 'Shareholder / equity contribution',
                'bank_finance'    => 'Bank / trade finance',
                'inheritance'     => 'Inheritance',
                'other'           => 'Other',
            ],
            'source_of_wealth'    => [
                'uae_business'    => 'UAE business operations',
                'foreign_business'=> 'Foreign business operations',
                'salary'          => 'Salary / employment',
                'real_estate'     => 'Real estate',
                'inheritance'     => 'Inheritance',
                'savings'         => 'Savings',
                'investment'      => 'Investment returns',
                'other'           => 'Other',
            ],
            'declarations_corporate' => ['pep','source_of_funds','sanctions','ubo','beneficial_ownership'],
            'declarations_individual'=> ['pep','source_of_funds','sanctions'],
            'risk_categories'     => ['customer','geographic','service','transaction','channel'],
            'extra_fields'        => [
                'service_type'         => ['label'=>'Services required', 'type'=>'select', 'options'=>['company_formation'=>'Company formation','registered_agent'=>'Registered agent / address','nominee'=>'Nominee director / shareholder','secretarial'=>'Company secretarial','other'=>'Other CSP services']],
                'jurisdiction'         => ['label'=>'Jurisdiction of company', 'type'=>'text'],
                'ultimate_purpose'     => ['label'=>'Ultimate purpose of company', 'type'=>'text'],
            ],
            'corporate_doc_types' => [
                ['type'=>'trade_license',      'label'=>'Trade licence',                'has_expiry'=>true],
                ['type'=>'moa',                'label'=>'Memorandum of Association',    'has_expiry'=>false],
                ['type'=>'passport',           'label'=>'Passport (signatory)',         'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID (signatory)',      'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'ubo_register',       'label'=>'UBO register / declaration',  'has_expiry'=>false],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
            'individual_doc_types' => [
                ['type'=>'passport',           'label'=>'Passport',                     'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID',                  'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
        ];
    }

    // ── ACCOUNTING & AUDITING ──────────────────────────────────────────────

    private static function accounting(): array
    {
        return [
            'label'        => 'Accountants & Auditors',
            'client_types' => [
                'corporate' => 'Corporate',
                'individual' => 'Individual',
            ],
            'show_ejari'          => false,
            'show_supply_chain'   => false,
            'show_cahra'          => false,
            'show_expected_volume'=> true,
            'goaml_threshold'     => 55000,
            'goaml_reason'        => 'Transaction Above AED 55000',
            'source_of_funds'     => [
                'business_income' => 'Business income',
                'salary'          => 'Salary / employment income',
                'professional_fees'=> 'Professional fees',
                'investment'      => 'Investment returns',
                'bank_finance'    => 'Bank / trade finance',
                'other'           => 'Other',
            ],
            'source_of_wealth'    => [
                'uae_business'    => 'UAE business operations',
                'foreign_business'=> 'Foreign business operations',
                'salary'          => 'Salary / employment',
                'real_estate'     => 'Real estate',
                'professional'    => 'Professional practice',
                'inheritance'     => 'Inheritance',
                'savings'         => 'Savings',
                'other'           => 'Other',
            ],
            'declarations_corporate' => ['pep','source_of_funds','sanctions','ubo','client_funds'],
            'declarations_individual'=> ['pep','source_of_funds','sanctions'],
            'risk_categories'     => ['customer','geographic','service','transaction','channel'],
            'extra_fields'        => [
                'service_type'         => ['label'=>'Services provided', 'type'=>'select', 'options'=>['audit'=>'Statutory audit','review'=>'Review engagement','compilation'=>'Compilation','tax'=>'Tax advisory','bookkeeping'=>'Bookkeeping','vat'=>'VAT advisory','other'=>'Other']],
                'client_handles_funds' => ['label'=>'Does client handle third-party funds?', 'type'=>'select', 'options'=>['no'=>'No','yes'=>'Yes — details provided']],
                'regulated_entity'     => ['label'=>'Is client a regulated entity?', 'type'=>'select', 'options'=>['no'=>'No','yes_uae'=>'Yes — UAE regulated','yes_foreign'=>'Yes — foreign regulated']],
            ],
            'corporate_doc_types' => [
                ['type'=>'trade_license',      'label'=>'Trade licence',                'has_expiry'=>true],
                ['type'=>'moa',                'label'=>'Memorandum of Association',    'has_expiry'=>false],
                ['type'=>'passport',           'label'=>'Passport (signatory)',         'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID (signatory)',      'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'pi_insurance',       'label'=>'Professional indemnity insurance','has_expiry'=>true],
                ['type'=>'regulatory_licence', 'label'=>'Regulatory / professional licence','has_expiry'=>true],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
            'individual_doc_types' => [
                ['type'=>'passport',           'label'=>'Passport',                     'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID',                  'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'pi_insurance',       'label'=>'Professional indemnity insurance','has_expiry'=>true],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
        ];
    }

    // ── OTHER DNFBP ────────────────────────────────────────────────────────

    private static function other(): array
    {
        return [
            'label'        => 'Other DNFBP',
            'client_types' => [
                'corporate' => 'Corporate',
                'individual' => 'Individual',
            ],
            'show_ejari'          => false,
            'show_supply_chain'   => false,
            'show_cahra'          => false,
            'show_expected_volume'=> true,
            'goaml_threshold'     => 55000,
            'goaml_reason'        => 'Transaction Above AED 55000',
            'source_of_funds'     => [
                'business_income' => 'Business income',
                'salary'          => 'Salary / employment income',
                'investment'      => 'Investment returns',
                'bank_finance'    => 'Bank / trade finance',
                'inheritance'     => 'Inheritance',
                'savings'         => 'Savings',
                'other'           => 'Other',
            ],
            'source_of_wealth'    => [
                'uae_business'    => 'UAE business operations',
                'foreign_business'=> 'Foreign business operations',
                'salary'          => 'Salary / employment',
                'real_estate'     => 'Real estate',
                'inheritance'     => 'Inheritance',
                'savings'         => 'Savings',
                'investment'      => 'Investment returns',
                'other'           => 'Other',
            ],
            'declarations_corporate' => ['pep','source_of_funds','sanctions','ubo'],
            'declarations_individual'=> ['pep','source_of_funds','sanctions'],
            'risk_categories'     => ['customer','geographic','product','transaction','channel'],
            'corporate_doc_types' => [
                ['type'=>'trade_license',      'label'=>'Trade licence',                'has_expiry'=>true],
                ['type'=>'moa',                'label'=>'Memorandum of Association',    'has_expiry'=>false],
                ['type'=>'passport',           'label'=>'Passport (signatory)',         'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID (signatory)',      'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
            'individual_doc_types' => [
                ['type'=>'passport',           'label'=>'Passport',                     'has_expiry'=>true],
                ['type'=>'eid',                'label'=>'Emirates ID',                  'has_expiry'=>true],
                ['type'=>'source_of_funds',    'label'=>'Source of funds evidence',    'has_expiry'=>false],
                ['type'=>'other',              'label'=>'Other',                        'has_expiry'=>false],
            ],
        ];
    }
}
