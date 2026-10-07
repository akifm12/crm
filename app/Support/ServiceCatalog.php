<?php
// app/Support/ServiceCatalog.php

namespace App\Support;

class ServiceCatalog
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
                'summary' => 'For gold, precious metals and stones dealers. We handle customer onboarding, goAML DPMSR and STR filing, and sanctions screening, whether you trade locally, import or export.',
            ],
            [
                'slug' => 'real_estate', 'icon' => '🏢', 'portal_backed' => true,
                'title' => 'Real Estate',
                'summary' => 'For property brokers and agents. Customer checks, proof of where the money comes from, and monitoring of your deals.',
            ],
            [
                'slug' => 'company_services', 'icon' => '🏛', 'portal_backed' => true,
                'title' => 'Company Service Providers',
                'summary' => 'For company formation, registered agent and nominee services. Ultimate-owner declarations, high-risk-area screening and trade licence checks.',
            ],
            [
                'slug' => 'accounting', 'icon' => '🧾', 'portal_backed' => true,
                'title' => 'Accounting & Auditing',
                'summary' => 'For accountants and auditors. Risk assessment, client screening and help with your regulatory reports.',
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
                'summary' => 'The platform we run our own compliance service on. Customers fill in their own details through a secure link, documents are read automatically, and screening happens in the same place.',
                'features' => [
                    'Secure, single-use onboarding links your customers fill in themselves, with no login needed',
                    'Document scanning that pulls names, dates and ID numbers out of passports, Emirates IDs and trade licences',
                    'Sector-specific checklists for bullion, real estate, company services and accounting',
                    'Sanctions & PEP screening via Blue Arrow\'s proprietary Sentinel engine',
                    'Risk assessment workflow with periodic review scheduling',
                    'goAML STR/SAR/DPMSR XML report generation for UAE FIU filing',
                ],
            ],
            [
                'icon' => '🔎', 'badge' => 'Core Product',
                'title' => 'Blue Arrow Sentinel: AML Screening Software',
                'summary' => 'Our own sanctions, PEP and adverse-media screening software. It does the screening inside our KYC portal, and you can also use it on its own. There\'s a free trial.',
                'link' => 'sentinel',
                'features' => [
                    'Sanctions & PEP screening against OFAC, EU, UK, UN, the UAE Local Terrorist List and 100+ other programmes, updated nightly',
                    'Adverse media checked in the same search',
                    'Fuzzy name-matching to catch transliteration and spelling variants',
                    'Ongoing re-screening of existing clients, not just at onboarding',
                    'Case review workflow with MLRO decisions and case reports',
                    'Use it on its own or inside the KYC Portal, with the same results and reports in both',
                ],
            ],
            [
                'icon' => '📊', 'badge' => 'Core Product',
                'title' => 'Bullion Accounting Software',
                'summary' => 'A full double-entry accounting suite built specifically for precious metals trading, with weight-and-purity aware invoicing and inventory.',
                'features' => [
                    'Hierarchical chart of accounts with system and custom accounts',
                    'Double-entry general ledger with balance validation and reversal support',
                    'VAT-aware, metal-priced invoicing for fixed and unfixed (price-TBD) deals, with making charges and multiple currencies',
                    'Inventory tracking by weight and pieces across gold, silver, platinum and palladium',
                    'Client payments, deposits and margin tracking',
                    'Trial Balance, Balance Sheet, Income Statement, VAT Summary and Client Statement reports, exported as PDF or CSV',
                ],
            ],
            [
                'icon' => '💻', 'badge' => 'Advisory',
                'title' => 'Custom Software & App Development',
                'summary' => 'If our own products don\'t fit, we can build something that does, for compliance or any other part of your business.',
                'features' => [
                    'Custom web portals and internal tools',
                    'Mobile app development',
                    'Integrations with existing business systems',
                    'Ongoing support and maintenance',
                ],
            ],
        ];
    }
}
