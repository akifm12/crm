<?php
// app/Support/OtcChartOfAccountTemplate.php — default B-Book chart of accounts.
// Mirrors the bullion_dealer A-Book template in shape, minus VAT (OTC deals are
// generic, not tax invoices). Lives entirely in otc_chart_of_accounts — see
// migration for why this is a separate table from ChartOfAccountTemplates.

namespace App\Support;

class OtcChartOfAccountTemplate
{
    public static function get(): array
    {
        return [
            // ── Assets ──────────────────────────────────────────────────────
            ['code' => '1000', 'name' => 'Assets', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => null, 'parent_code' => null],
            ['code' => '1010', 'name' => 'Cash on Hand', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'cash', 'parent_code' => '1000'],
            ['code' => '1020', 'name' => 'Bank Account', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'bank', 'parent_code' => '1000'],
            ['code' => '1030', 'name' => 'Counterparty Receivables', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'ar', 'parent_code' => '1000'],
            ['code' => '1050', 'name' => 'Deposits / Advances Given', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'supplier_deposits', 'parent_code' => '1000'],
            ['code' => '1100', 'name' => 'Gold Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_gold', 'parent_code' => '1000'],
            ['code' => '1101', 'name' => 'Silver Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_silver', 'parent_code' => '1000'],
            ['code' => '1102', 'name' => 'Platinum Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_platinum', 'parent_code' => '1000'],
            ['code' => '1103', 'name' => 'Palladium Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'subtype' => 'inventory_palladium', 'parent_code' => '1000'],

            // ── Liabilities ─────────────────────────────────────────────────
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => null, 'parent_code' => null],
            ['code' => '2010', 'name' => 'Counterparty Payables', 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'ap', 'parent_code' => '2000'],
            ['code' => '2030', 'name' => 'Deposits / Advances Received', 'type' => 'liability', 'normal_balance' => 'credit', 'subtype' => 'customer_deposits', 'parent_code' => '2000'],

            // ── Equity ──────────────────────────────────────────────────────
            ['code' => '3000', 'name' => 'Equity', 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => null, 'parent_code' => null],
            ['code' => '3010', 'name' => "Owner's Equity", 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => 'owners_equity', 'parent_code' => '3000'],
            ['code' => '3020', 'name' => 'Retained Earnings', 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => 'retained_earnings', 'parent_code' => '3000'],
            ['code' => '3030', 'name' => 'Inventory Adjustments', 'type' => 'equity', 'normal_balance' => 'credit', 'subtype' => 'inventory_adjustment', 'parent_code' => '3000'],

            // ── Income ──────────────────────────────────────────────────────
            ['code' => '4000', 'name' => 'Income', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => null, 'parent_code' => null],
            ['code' => '4010', 'name' => 'OTC Revenue — Gold', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_gold', 'parent_code' => '4000'],
            ['code' => '4011', 'name' => 'OTC Revenue — Silver', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_silver', 'parent_code' => '4000'],
            ['code' => '4012', 'name' => 'OTC Revenue — Platinum', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_platinum', 'parent_code' => '4000'],
            ['code' => '4013', 'name' => 'OTC Revenue — Palladium', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'sales_palladium', 'parent_code' => '4000'],
            ['code' => '4020', 'name' => 'Metal Exchange Gain/Loss', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'exchange_gain_loss', 'parent_code' => '4000'],
            ['code' => '4030', 'name' => 'FX Gain/Loss', 'type' => 'income', 'normal_balance' => 'credit', 'subtype' => 'fx_gain_loss', 'parent_code' => '4000'],

            // ── Expenses ────────────────────────────────────────────────────
            ['code' => '5000', 'name' => 'Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => null, 'parent_code' => null],
            ['code' => '5010', 'name' => 'Cost of Goods Sold — Gold', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_gold', 'parent_code' => '5000'],
            ['code' => '5011', 'name' => 'Cost of Goods Sold — Silver', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_silver', 'parent_code' => '5000'],
            ['code' => '5012', 'name' => 'Cost of Goods Sold — Platinum', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_platinum', 'parent_code' => '5000'],
            ['code' => '5013', 'name' => 'Cost of Goods Sold — Palladium', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'cogs_palladium', 'parent_code' => '5000'],
            ['code' => '5040', 'name' => 'Other Operating Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'other_expense', 'parent_code' => '5000'],
            ['code' => '5900', 'name' => 'FX Rounding Adjustment', 'type' => 'expense', 'normal_balance' => 'debit', 'subtype' => 'rounding', 'parent_code' => '5000'],
        ];
    }
}
