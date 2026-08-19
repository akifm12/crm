<?php
// app/Support/ChartOfAccountTemplates.php

namespace App\Support;

class ChartOfAccountTemplates
{
    public static function get(string $template): array
    {
        return match ($template) {
            'bullion_dealer'  => static::bullionDealer(),
            'general_business'=> static::generalBusiness(),
            'real_estate'     => static::realEstate(),
            default           => [],
        };
    }

    /**
     * Default chart of accounts for a gold bullion dealer.
     * Each row: code, name, type, normal_balance, subtype (nullable), parent_code (nullable).
     * `subtype` is a stable lookup key used by LedgerService/InventoryService to find the
     * right account programmatically (e.g. where a COGS or inventory posting should land)
     * without hardcoding account IDs.
     */
    private static function generalBusiness(): array
    {
        return [
            // Assets
            ['code' => '1000', 'name' => 'Assets',                   'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => null],
            ['code' => '1010', 'name' => 'Cash on Hand',             'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'cash',              'parent_code' => '1000'],
            ['code' => '1020', 'name' => 'Bank Account',             'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'bank',              'parent_code' => '1000'],
            ['code' => '1030', 'name' => 'Accounts Receivable',      'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'ar',                'parent_code' => '1000'],
            ['code' => '1040', 'name' => 'VAT Recoverable',          'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'vat_recoverable',   'parent_code' => '1000'],
            ['code' => '1050', 'name' => 'Prepaid Expenses',         'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '1000'],
            ['code' => '1100', 'name' => 'Fixed Assets',             'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '1000'],
            ['code' => '1110', 'name' => 'Accumulated Depreciation', 'type' => 'asset',     'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => '1100'],
            // Liabilities
            ['code' => '2000', 'name' => 'Liabilities',              'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => null],
            ['code' => '2010', 'name' => 'Accounts Payable',         'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'ap',                'parent_code' => '2000'],
            ['code' => '2020', 'name' => 'VAT Payable',              'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'vat_payable',       'parent_code' => '2000'],
            ['code' => '2030', 'name' => 'Loans Payable',            'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => '2000'],
            // Equity
            ['code' => '3000', 'name' => 'Equity',                   'type' => 'equity',    'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => null],
            ['code' => '3010', 'name' => "Owner's Equity",           'type' => 'equity',    'normal_balance' => 'credit', 'subtype' => 'owners_equity',     'parent_code' => '3000'],
            ['code' => '3020', 'name' => 'Retained Earnings',        'type' => 'equity',    'normal_balance' => 'credit', 'subtype' => 'retained_earnings', 'parent_code' => '3000'],
            // Income
            ['code' => '4000', 'name' => 'Income',                   'type' => 'income',    'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => null],
            ['code' => '4010', 'name' => 'Sales Revenue',            'type' => 'income',    'normal_balance' => 'credit', 'subtype' => 'sales_revenue',     'parent_code' => '4000'],
            ['code' => '4020', 'name' => 'Service Revenue',          'type' => 'income',    'normal_balance' => 'credit', 'subtype' => 'service_revenue',   'parent_code' => '4000'],
            ['code' => '4030', 'name' => 'Other Income',             'type' => 'income',    'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => '4000'],
            // Expenses
            ['code' => '5000', 'name' => 'Expenses',                 'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => null],
            ['code' => '5010', 'name' => 'Cost of Goods Sold',       'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => 'cogs',              'parent_code' => '5000'],
            ['code' => '5020', 'name' => 'Salaries & Wages',         'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5030', 'name' => 'Rent Expense',             'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5040', 'name' => 'Utilities',                'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5050', 'name' => 'Marketing & Advertising',  'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5060', 'name' => 'Professional Fees',        'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5070', 'name' => 'Depreciation',             'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5080', 'name' => 'Other Operating Expenses', 'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => 'other_expense',     'parent_code' => '5000'],
        ];
    }

    private static function realEstate(): array
    {
        return [
            // Assets
            ['code' => '1000', 'name' => 'Assets',                      'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => null],
            ['code' => '1010', 'name' => 'Cash on Hand',                'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'cash',              'parent_code' => '1000'],
            ['code' => '1020', 'name' => 'Bank Account',                'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'bank',              'parent_code' => '1000'],
            ['code' => '1030', 'name' => 'Accounts Receivable',         'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'ar',                'parent_code' => '1000'],
            ['code' => '1040', 'name' => 'VAT Recoverable',             'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => 'vat_recoverable',   'parent_code' => '1000'],
            ['code' => '1050', 'name' => 'Tenant Security Deposits',    'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '1000'],
            ['code' => '1100', 'name' => 'Land & Buildings',            'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '1000'],
            ['code' => '1110', 'name' => 'Accumulated Depreciation',    'type' => 'asset',     'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => '1100'],
            ['code' => '1120', 'name' => 'Furniture & Fixtures',        'type' => 'asset',     'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '1000'],
            // Liabilities
            ['code' => '2000', 'name' => 'Liabilities',                 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => null],
            ['code' => '2010', 'name' => 'Accounts Payable',            'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'ap',                'parent_code' => '2000'],
            ['code' => '2020', 'name' => 'VAT Payable',                 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'vat_payable',       'parent_code' => '2000'],
            ['code' => '2030', 'name' => 'Security Deposits Held',      'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => '2000'],
            ['code' => '2040', 'name' => 'Mortgage / Loan Payable',     'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => '2000'],
            ['code' => '2050', 'name' => 'Advance Rent Received',       'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'customer_deposits', 'parent_code' => '2000'],
            // Equity
            ['code' => '3000', 'name' => 'Equity',                      'type' => 'equity',    'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => null],
            ['code' => '3010', 'name' => "Owner's Equity",              'type' => 'equity',    'normal_balance' => 'credit', 'subtype' => 'owners_equity',     'parent_code' => '3000'],
            ['code' => '3020', 'name' => 'Retained Earnings',           'type' => 'equity',    'normal_balance' => 'credit', 'subtype' => 'retained_earnings', 'parent_code' => '3000'],
            // Income
            ['code' => '4000', 'name' => 'Income',                      'type' => 'income',    'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => null],
            ['code' => '4010', 'name' => 'Rental Income',               'type' => 'income',    'normal_balance' => 'credit', 'subtype' => 'sales_revenue',     'parent_code' => '4000'],
            ['code' => '4020', 'name' => 'Service Charge Income',       'type' => 'income',    'normal_balance' => 'credit', 'subtype' => 'service_revenue',   'parent_code' => '4000'],
            ['code' => '4030', 'name' => 'Other Property Income',       'type' => 'income',    'normal_balance' => 'credit', 'subtype' => null,                'parent_code' => '4000'],
            // Expenses
            ['code' => '5000', 'name' => 'Expenses',                    'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => null],
            ['code' => '5010', 'name' => 'Maintenance & Repairs',       'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5020', 'name' => 'Property Management Fees',    'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5030', 'name' => 'Property Insurance',          'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5040', 'name' => 'Utilities',                   'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5050', 'name' => 'Salaries & Wages',            'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5060', 'name' => 'Depreciation',                'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5070', 'name' => 'Professional & Legal Fees',   'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => null,                'parent_code' => '5000'],
            ['code' => '5080', 'name' => 'Other Property Expenses',     'type' => 'expense',   'normal_balance' => 'debit',  'subtype' => 'other_expense',     'parent_code' => '5000'],
        ];
    }

    private static function bullionDealer(): array
    {
        return [
            // ── Assets ──────────────────────────────────────────────────────
            ['code' => '1000', 'name' => 'Assets', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => null, 'parent_code' => null],
            ['code' => '1010', 'name' => 'Cash on Hand', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'cash', 'parent_code' => '1000'],
            ['code' => '1020', 'name' => 'Bank Account', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'bank', 'parent_code' => '1000'],
            ['code' => '1030', 'name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'ar', 'parent_code' => '1000'],
            ['code' => '1040', 'name' => 'VAT Recoverable', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'vat_recoverable', 'parent_code' => '1000'],
            ['code' => '1050', 'name' => 'Supplier Advances / Deposits', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'supplier_deposits', 'parent_code' => '1000'],
            ['code' => '1100', 'name' => 'Gold Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_gold', 'parent_code' => '1000'],
            ['code' => '1101', 'name' => 'Silver Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_silver', 'parent_code' => '1000'],
            ['code' => '1102', 'name' => 'Platinum Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_platinum', 'parent_code' => '1000'],
            ['code' => '1103', 'name' => 'Palladium Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_palladium', 'parent_code' => '1000'],

            // ── Liabilities ─────────────────────────────────────────────────
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => null, 'parent_code' => null],
            ['code' => '2010', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'ap', 'parent_code' => '2000'],
            ['code' => '2020', 'name' => 'VAT Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'vat_payable', 'parent_code' => '2000'],
            ['code' => '2030', 'name' => 'Customer Deposits / Advances', 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'customer_deposits', 'parent_code' => '2000'],

            // ── Equity ──────────────────────────────────────────────────────
            ['code' => '3000', 'name' => 'Equity', 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => null, 'parent_code' => null],
            ['code' => '3010', 'name' => "Owner's Equity", 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => 'owners_equity', 'parent_code' => '3000'],
            ['code' => '3020', 'name' => 'Retained Earnings', 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => 'retained_earnings', 'parent_code' => '3000'],
            // Stock-count corrections adjust equity directly, not income — a physical
            // count discrepancy isn't a sale, so it must never inflate net profit.
            ['code' => '3030', 'name' => 'Inventory Adjustments', 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => 'inventory_adjustment', 'parent_code' => '3000'],

            // ── Income ──────────────────────────────────────────────────────
            ['code' => '4000', 'name' => 'Income', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => null, 'parent_code' => null],
            ['code' => '4010', 'name' => 'Sales Revenue — Gold', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_gold', 'parent_code' => '4000'],
            ['code' => '4011', 'name' => 'Sales Revenue — Silver', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_silver', 'parent_code' => '4000'],
            ['code' => '4012', 'name' => 'Sales Revenue — Platinum', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_platinum', 'parent_code' => '4000'],
            ['code' => '4013', 'name' => 'Sales Revenue — Palladium', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_palladium', 'parent_code' => '4000'],
            ['code' => '4020', 'name' => 'Metal Exchange Gain/Loss', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'exchange_gain_loss', 'parent_code' => '4000'],
            ['code' => '4030', 'name' => 'FX Gain/Loss', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'fx_gain_loss', 'parent_code' => '4000'],

            // ── Expenses ────────────────────────────────────────────────────
            ['code' => '5000', 'name' => 'Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => null, 'parent_code' => null],
            ['code' => '5010', 'name' => 'Cost of Goods Sold — Gold', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_gold', 'parent_code' => '5000'],
            ['code' => '5011', 'name' => 'Cost of Goods Sold — Silver', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_silver', 'parent_code' => '5000'],
            ['code' => '5012', 'name' => 'Cost of Goods Sold — Platinum', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_platinum', 'parent_code' => '5000'],
            ['code' => '5013', 'name' => 'Cost of Goods Sold — Palladium', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_palladium', 'parent_code' => '5000'],
            ['code' => '5020', 'name' => 'Rent Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => null, 'parent_code' => '5000'],
            ['code' => '5030', 'name' => 'Salaries & Wages', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => null, 'parent_code' => '5000'],
            ['code' => '5040', 'name' => 'Other Operating Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'other_expense', 'parent_code' => '5000'],
            ['code' => '5900', 'name' => 'FX Rounding Adjustment', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'rounding', 'parent_code' => '5000'],
        ];
    }
}
