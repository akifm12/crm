<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportEltorro extends Command
{
    protected $signature   = 'import:eltorro
                                {--sql= : Path to SQL dump (default: C:/Users/akif/Downloads/eltorro_export.sql)}
                                {--dry-run : Parse and count records without writing anything}
                                {--accounts-only : Import chart of accounts only — skip journal entries, invoices, and bills}
                                {--wipe : Delete existing Eltorro data first (use with care)}';
    protected $description = 'Import Eltorro Real Estate data from a standalone-accounting PostgreSQL dump into bluearrow-portal';

    private const SOURCE_COMPANY  = 2;
    private const TENANT_SLUG     = 'eltorro';
    private const TENANT_NAME     = 'Eltorro Real Estate Brokerage LLC';
    private const TENANT_TRN      = '104159813500003';
    private const SQL_DEFAULT     = 'C:/Users/akif/Downloads/eltorro_export.sql';

    // ------------------------------------------------------------------
    // Type map: PostgreSQL account_type  →  bluearrow type
    // ------------------------------------------------------------------
    private const TYPE_MAP = [
        'ASSET'     => 'asset',
        'LIABILITY' => 'liability',
        'EQUITY'    => 'equity',
        'REVENUE'   => 'income',
        'EXPENSE'   => 'expense',
    ];

    // ------------------------------------------------------------------
    // handle()
    // ------------------------------------------------------------------
    public function handle(): int
    {
        $sqlFile = $this->option('sql') ?: self::SQL_DEFAULT;
        $dry          = (bool) $this->option('dry-run');
        $wipe         = (bool) $this->option('wipe');
        $accountsOnly = (bool) $this->option('accounts-only');

        if (!file_exists($sqlFile)) {
            $this->error("SQL dump not found: {$sqlFile}");
            return 1;
        }

        $this->info("Reading {$sqlFile} …");
        $lines = file($sqlFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // ── Parse each relevant table ──────────────────────────────────
        $srcAccounts = $this->parseTable($lines, 'accounts',
            ['id','company_id','parent_id','code','name','name_arabic','description',
             'account_type','account_sub_type','normal_balance','is_control',
             'is_bank','is_cash','is_receivable','is_payable','is_vat_account',
             'currency_code','is_active','notes','opening_balance',
             'opening_balance_date','created_at','updated_at'],
            fn($r) => (int)$r['company_id'] === self::SOURCE_COMPANY
        );

        if ($accountsOnly) {
            $this->table(['Table', 'Records'], [
                ['accounts', count($srcAccounts)],
            ]);
            if ($dry) { $this->info('--dry-run: nothing written.'); return 0; }
            if (!$this->confirm('Import chart of accounts only (existing codes will be skipped)?', true)) { return 0; }
            $now = now();
            DB::transaction(function () use ($srcAccounts, $now) {
                $tenant = DB::table('tenants')
                    ->where('slug', self::TENANT_SLUG)
                    ->orWhere('name', self::TENANT_NAME)
                    ->first();
                if (!$tenant) { $this->error('Eltorro tenant not found. Create it first.'); return; }
                $tenantId = $tenant->id;
                $this->info("Using tenant ID {$tenantId}: {$tenant->name}");
                $this->insertAccounts($srcAccounts, $tenantId, $now);
            });
            $this->info('Import complete.');
            return 0;
        }

        $srcJEs = $this->parseTable($lines, 'journal_entries',
            ['id','company_id','period_id','entry_number','entry_date',
             'journal_type','status','reference','description','narration',
             'source_type','source_id','total_debit','total_credit',
             'currency_code','exchange_rate','is_reversal','reversed_entry_id',
             'is_recurring','recurring_template_id','created_by_id',
             'updated_by_id','is_deleted','deleted_at','deleted_by_id',
             'created_at','updated_at'],
            fn($r) => (int)$r['company_id'] === self::SOURCE_COMPANY
                   && $r['status'] === 'POSTED'
                   && $r['is_deleted'] === 'false'
        );
        $srcJEIds = array_column($srcJEs, null, 'id');

        $srcJLines = $this->parseTable($lines, 'journal_lines',
            ['id','journal_entry_id','company_id','line_number','account_id',
             'cost_center_id','description','debit_amount','credit_amount',
             'fc_debit_amount','fc_credit_amount','currency_code','exchange_rate',
             'is_reconciled','reconciled_at','reconciliation_id',
             'customer_id','vendor_id','tax_code_id','vat_amount'],
            fn($r) => (int)$r['company_id'] === self::SOURCE_COMPANY
                   && isset($srcJEIds[$r['journal_entry_id']])
        );

        $srcInvoices = $this->parseTable($lines, 'invoices',
            ['id','company_id','customer_id','period_id','journal_entry_id',
             'invoice_number','invoice_type','invoice_date','supply_date',
             'due_date','original_invoice_id','reason_for_note','status',
             'supplier_name','supplier_trn','supplier_address',
             'customer_name','customer_trn','customer_address',
             'currency_code','exchange_rate','subtotal','discount_amount',
             'taxable_amount','vat_amount_standard','vat_amount_zero',
             'vat_amount_exempt','total_vat_amount','total_amount',
             'amount_paid','amount_due','payment_terms','notes','terms',
             'po_number','is_reverse_charge','qr_code_data','pdf_url',
             'created_by_id','updated_by_id','is_deleted','deleted_at',
             'deleted_by_id','created_at','updated_at'],
            fn($r) => (int)$r['company_id'] === self::SOURCE_COMPANY
                   && $r['is_deleted'] === 'false'
        );

        $srcInvIds = array_column($srcInvoices, null, 'id');

        $srcInvLines = $this->parseTable($lines, 'invoice_lines',
            ['id','invoice_id','company_id','line_number','item_id',
             'account_id','cost_center_id','tax_code_id','description',
             'quantity','unit_of_measure','unit_price','discount_percent',
             'discount_amount','line_amount','supply_type','vat_rate',
             'vat_amount','total_amount','project_id'],
            fn($r) => (int)$r['company_id'] === self::SOURCE_COMPANY
                   && isset($srcInvIds[$r['invoice_id']])
        );

        $srcBills = $this->parseTable($lines, 'bills',
            ['id','company_id','vendor_id','period_id','journal_entry_id',
             'bill_number','vendor_ref','bill_type','bill_date','due_date',
             'supply_date','original_bill_id','status','currency_code',
             'exchange_rate','vendor_name','vendor_trn','subtotal',
             'discount_amount','total_vat_amount','total_amount',
             'amount_paid','amount_due','is_reverse_charge','reverse_charge_vat',
             'notes','pdf_url','created_by_id','updated_by_id',
             'is_deleted','deleted_at','deleted_by_id','created_at','updated_at'],
            fn($r) => (int)$r['company_id'] === self::SOURCE_COMPANY
                   && $r['is_deleted'] === 'false'
        );

        $srcBillIds = array_column($srcBills, null, 'id');

        $srcBillLines = $this->parseTable($lines, 'bill_lines',
            ['id','bill_id','company_id','line_number','item_id',
             'account_id','cost_center_id','tax_code_id','description',
             'quantity','unit_of_measure','unit_price','discount_percent',
             'line_amount','supply_type','vat_rate','vat_amount',
             'total_amount','is_reverse_charge'],
            fn($r) => (int)$r['company_id'] === self::SOURCE_COMPANY
                   && isset($srcBillIds[$r['bill_id']])
        );

        // ── Summary ────────────────────────────────────────────────────
        $this->table(['Table', 'Records'], [
            ['accounts',       count($srcAccounts)],
            ['journal_entries',count($srcJEs)],
            ['journal_lines',  count($srcJLines)],
            ['invoices',       count($srcInvoices)],
            ['invoice_lines',  count($srcInvLines)],
            ['bills',          count($srcBills)],
            ['bill_lines',     count($srcBillLines)],
        ]);

        if ($dry) {
            $this->info('--dry-run: nothing written.');
            return 0;
        }

        if (!$this->confirm('Proceed with import?', true)) {
            return 0;
        }

        // ── Write in a transaction ─────────────────────────────────────
        DB::transaction(function () use (
            $srcAccounts, $srcJEs, $srcJLines,
            $srcInvoices, $srcInvLines, $srcBills, $srcBillLines, $wipe
        ) {
            $now = now();

            // ── 1. Find or create tenant ───────────────────────────────
            $tenant = DB::table('tenants')
                ->where('slug', self::TENANT_SLUG)
                ->orWhere('name', self::TENANT_NAME)
                ->first();

            if (!$tenant) {
                $tenantId = DB::table('tenants')->insertGetId([
                    'name'          => self::TENANT_NAME,
                    'slug'          => self::TENANT_SLUG,
                    'business_type' => 'real_estate',
                    'vat_trn'       => self::TENANT_TRN,
                    'is_active'     => true,
                    'settings'      => json_encode([
                        'enabled_modules' => [
                            'real_estate_accounting' => true,
                        ],
                    ]),
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $this->info("Created tenant ID {$tenantId}: " . self::TENANT_NAME);
            } else {
                $tenantId = $tenant->id;
                $this->info("Using existing tenant ID {$tenantId}: {$tenant->name}");
            }

            if ($wipe) {
                $this->warn("Wiping existing Eltorro accounting data for tenant {$tenantId} …");
                DB::table('standard_invoice_lines')
                    ->whereIn('standard_invoice_id',
                        DB::table('standard_invoices')->where('tenant_id', $tenantId)->pluck('id'))
                    ->delete();
                DB::table('standard_invoices')->where('tenant_id', $tenantId)->delete();
                DB::table('bill_lines')
                    ->whereIn('bill_id',
                        DB::table('bills')->where('tenant_id', $tenantId)->pluck('id'))
                    ->delete();
                DB::table('bills')->where('tenant_id', $tenantId)->delete();
                DB::table('journal_entry_lines')
                    ->whereIn('journal_entry_id',
                        DB::table('journal_entries')->where('tenant_id', $tenantId)->pluck('id'))
                    ->delete();
                DB::table('journal_entries')->where('tenant_id', $tenantId)->delete();
                DB::table('chart_of_accounts')->where('tenant_id', $tenantId)->delete();
            }

            // ── 2. Chart of accounts ───────────────────────────────────
            $srcIdToNewId = $this->insertAccounts($srcAccounts, $tenantId, $now);

            // ── 3. Journal entries + lines ─────────────────────────────
            $srcJEToNewJE = [];  // source JE id → new JE id

            foreach ($srcJEs as $je) {
                $newJEId = DB::table('journal_entries')->insertGetId([
                    'tenant_id'    => $tenantId,
                    'entry_date'   => substr($je['entry_date'], 0, 10),
                    'reference'    => $je['reference'] ?? $je['entry_number'],
                    'source_type'  => $je['journal_type'] ? strtolower($je['journal_type']) : null,
                    'source_id'    => null,
                    'currency_code'=> $je['currency_code'] ?? 'AED',
                    'exchange_rate'=> $je['exchange_rate'] ?? 1,
                    'status'       => 'posted',
                    'memo'         => $je['description'],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $srcJEToNewJE[$je['id']] = $newJEId;
            }

            // Build lines grouped by journal_entry_id
            $linesByJE = [];
            foreach ($srcJLines as $jl) {
                $linesByJE[$jl['journal_entry_id']][] = $jl;
            }

            $lineOrder = 0;
            foreach ($srcJEToNewJE as $srcJEId => $newJEId) {
                $lineOrder = 0;
                foreach ($linesByJE[$srcJEId] ?? [] as $jl) {
                    $coaId = $srcIdToNewId[$jl['account_id']] ?? null;
                    if (!$coaId) {
                        continue; // skip lines for unmapped accounts
                    }
                    DB::table('journal_entry_lines')->insert([
                        'journal_entry_id'    => $newJEId,
                        'chart_of_account_id' => $coaId,
                        'debit'               => (float)$jl['debit_amount'],
                        'credit'              => (float)$jl['credit_amount'],
                        'base_debit'          => (float)$jl['debit_amount'],
                        'base_credit'         => (float)$jl['credit_amount'],
                        'description'         => $jl['description'],
                        'line_order'          => $lineOrder++,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }
            }

            $this->info('  ✓ ' . count($srcJEs) . ' journal entries, ' . count($srcJLines) . ' lines imported');

            // ── 4. Standard invoices + lines ───────────────────────────
            $srcInvToNewInv = [];

            foreach ($srcInvoices as $inv) {
                $status = $this->mapInvoiceStatus($inv['status']);
                $newInvId = DB::table('standard_invoices')->insertGetId([
                    'tenant_id'        => $tenantId,
                    'module_type'      => 'real_estate',
                    'invoice_number'   => $inv['invoice_number'],
                    'client_name'      => $inv['customer_name'] ?? 'Unknown',
                    'client_vat_number'=> $inv['customer_trn'] ?? null,
                    'reference'        => $inv['po_number'],
                    'invoice_date'     => substr($inv['invoice_date'], 0, 10),
                    'due_date'         => $inv['due_date'] ? substr($inv['due_date'], 0, 10) : null,
                    'status'           => $status,
                    'subtotal'         => (float)($inv['subtotal'] ?? 0),
                    'vat_total'        => (float)($inv['total_vat_amount'] ?? 0),
                    'total'            => (float)($inv['total_amount'] ?? 0),
                    'amount_paid'      => (float)($inv['amount_paid'] ?? 0),
                    'notes'            => $inv['notes'],
                    'journal_entry_id' => $inv['journal_entry_id']
                                          ? ($srcJEToNewJE[$inv['journal_entry_id']] ?? null)
                                          : null,
                    'posted_at'        => in_array($status, ['posted']) ? $now : null,
                    'voided_at'        => $status === 'void' ? $now : null,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
                $srcInvToNewInv[$inv['id']] = $newInvId;
            }

            $lineOrder = 0;
            foreach ($srcInvLines as $il) {
                $newInvId = $srcInvToNewInv[$il['invoice_id']] ?? null;
                if (!$newInvId) {
                    continue;
                }
                DB::table('standard_invoice_lines')->insert([
                    'standard_invoice_id' => $newInvId,
                    'description'         => $il['description'] ?? 'Service',
                    'quantity'            => (float)($il['quantity'] ?? 1),
                    'unit_price'          => (float)($il['unit_price'] ?? 0),
                    'amount'              => (float)($il['line_amount'] ?? 0),
                    'vat_treatment'       => (float)($il['vat_rate'] ?? 0) > 0 ? 'standard' : 'zero',
                    'vat_rate'            => (float)($il['vat_rate'] ?? 0),
                    'vat_amount'          => (float)($il['vat_amount'] ?? 0),
                    'line_total'          => (float)($il['total_amount'] ?? 0),
                    'line_order'          => (int)($il['line_number'] ?? 0),
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }

            $this->info('  ✓ ' . count($srcInvoices) . ' invoices, ' . count($srcInvLines) . ' lines imported');

            // ── 5. Bills + bill_lines ──────────────────────────────────
            $srcBillToNewBill = [];

            foreach ($srcBills as $bill) {
                $status = $this->mapInvoiceStatus($bill['status']);
                $newBillId = DB::table('bills')->insertGetId([
                    'tenant_id'          => $tenantId,
                    'bill_number'        => $bill['bill_number'],
                    'supplier_name'      => $bill['vendor_name'] ?? 'Unknown',
                    'supplier_vat_number'=> $bill['vendor_trn'] ?? null,
                    'reference'          => $bill['vendor_ref'],
                    'bill_date'          => substr($bill['bill_date'], 0, 10),
                    'due_date'           => $bill['due_date'] ? substr($bill['due_date'], 0, 10) : null,
                    'status'             => $status,
                    'subtotal'           => (float)($bill['subtotal'] ?? 0),
                    'vat_total'          => (float)($bill['total_vat_amount'] ?? 0),
                    'total'              => (float)($bill['total_amount'] ?? 0),
                    'amount_paid'        => (float)($bill['amount_paid'] ?? 0),
                    'notes'              => $bill['notes'],
                    'journal_entry_id'   => $bill['journal_entry_id']
                                            ? ($srcJEToNewJE[$bill['journal_entry_id']] ?? null)
                                            : null,
                    'posted_at'          => in_array($status, ['posted']) ? $now : null,
                    'voided_at'          => $status === 'void' ? $now : null,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
                $srcBillToNewBill[$bill['id']] = $newBillId;
            }

            foreach ($srcBillLines as $bl) {
                $newBillId = $srcBillToNewBill[$bl['bill_id']] ?? null;
                if (!$newBillId) {
                    continue;
                }
                DB::table('bill_lines')->insert([
                    'bill_id'      => $newBillId,
                    'description'  => $bl['description'] ?? 'Expense',
                    'category'     => 'other',
                    'amount'       => (float)($bl['line_amount'] ?? 0),
                    'vat_treatment'=> (float)($bl['vat_rate'] ?? 0) > 0 ? 'standard' : 'zero',
                    'vat_rate'     => (float)($bl['vat_rate'] ?? 0),
                    'vat_amount'   => (float)($bl['vat_amount'] ?? 0),
                    'line_total'   => (float)($bl['total_amount'] ?? 0),
                    'line_order'   => (int)($bl['line_number'] ?? 0),
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }

            $this->info('  ✓ ' . count($srcBills) . ' bills, ' . count($srcBillLines) . ' lines imported');
        });

        $this->info('');
        $this->info('Import complete.');
        return 0;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Insert chart-of-accounts rows, skipping any code that already exists
     * for this tenant. Returns a map of source_id → new target id.
     */
    private function insertAccounts(array $srcAccounts, int $tenantId, $now): array
    {
        // Parents first
        usort($srcAccounts, fn($a, $b) =>
            ($a['parent_id'] === null ? 0 : 1) <=> ($b['parent_id'] === null ? 0 : 1)
        );

        $srcIdToNewId = [];

        // First pass: insert (skip duplicates by code)
        foreach ($srcAccounts as $acct) {
            $existing = DB::table('chart_of_accounts')
                ->where('tenant_id', $tenantId)
                ->where('code', $acct['code'])
                ->value('id');

            if ($existing) {
                $srcIdToNewId[$acct['id']] = $existing;
                continue; // already exists — don't overwrite
            }

            $newId = DB::table('chart_of_accounts')->insertGetId([
                'tenant_id'      => $tenantId,
                'parent_id'      => null,
                'code'           => $acct['code'],
                'name'           => $acct['name'],
                'type'           => self::TYPE_MAP[$acct['account_type']] ?? 'asset',
                'subtype'        => $this->mapSubtype($acct),
                'normal_balance' => strtolower($acct['normal_balance']),
                'is_system'      => false,
                'is_active'      => $acct['is_active'] !== 'false',
                'description'    => $acct['description'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
            $srcIdToNewId[$acct['id']] = $newId;
        }

        // Second pass: wire up parent_ids for newly inserted rows
        foreach ($srcAccounts as $acct) {
            if ($acct['parent_id'] !== null && isset($srcIdToNewId[$acct['parent_id']], $srcIdToNewId[$acct['id']])) {
                DB::table('chart_of_accounts')
                    ->where('id', $srcIdToNewId[$acct['id']])
                    ->whereNull('parent_id')
                    ->update(['parent_id' => $srcIdToNewId[$acct['parent_id']]]);
            }
        }

        $inserted = count(array_filter(array_keys($srcIdToNewId)));
        $this->info('  ✓ ' . count($srcAccounts) . ' accounts processed (' . $inserted . ' new, ' . (count($srcAccounts) - $inserted) . ' skipped — already existed)');

        return $srcIdToNewId;
    }

    private function mapSubtype(array $acct): ?string
    {
        if ($acct['is_receivable'] === 'true') return 'ar';
        if ($acct['is_payable']    === 'true') return 'ap';
        if ($acct['is_bank']       === 'true') return 'bank';
        if ($acct['is_cash']       === 'true') return 'cash';
        if ($acct['is_vat_account']=== 'true') {
            return $acct['account_type'] === 'ASSET' ? 'vat_recoverable' : 'vat_payable';
        }
        return null;
    }

    private function mapInvoiceStatus(string $src): string
    {
        return match (strtoupper($src)) {
            'PAID'    => 'posted',
            'POSTED'  => 'posted',
            'VOID'    => 'void',
            'DRAFT'   => 'draft',
            default   => 'draft',
        };
    }

    /**
     * Parse all INSERT lines for a given PostgreSQL table.
     * Returns an array of associative arrays keyed by $columns.
     *
     * @param  string[]  $lines
     * @param  string[]  $columns  Ordered list of column names matching the INSERT header
     * @param  callable  $filter   Returns true to keep the row
     */
    private function parseTable(array $lines, string $table, array $columns, callable $filter): array
    {
        $prefix = "INSERT INTO public.{$table} (";
        $rows   = [];

        foreach ($lines as $line) {
            if (!str_starts_with($line, $prefix)) {
                continue;
            }

            // Extract the VALUES (...) part
            $valStart = strpos($line, ' VALUES (');
            if ($valStart === false) {
                continue;
            }
            $valStr = substr($line, $valStart + 8); // includes opening '('
            $valStr = rtrim($valStr, ';');           // remove trailing ;

            $values = $this->parseValuesTuple($valStr);

            if (count($values) !== count($columns)) {
                // column count mismatch — skip malformed line
                continue;
            }

            $row = array_combine($columns, $values);

            if ($filter($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Parse a PostgreSQL VALUES tuple like (v1, 'text', NULL, true, 3.14)
     * Returns a flat array of PHP values (null for NULL, string for everything else).
     */
    private function parseValuesTuple(string $s): array
    {
        // Strip outer parentheses
        $s = ltrim($s, '(');
        $s = rtrim($s, ')');

        $result = [];
        $len    = strlen($s);
        $i      = 0;

        while ($i < $len) {
            // Skip leading whitespace
            while ($i < $len && $s[$i] === ' ') {
                $i++;
            }
            if ($i >= $len) {
                break;
            }

            if ($s[$i] === "'") {
                // Quoted string
                $i++;
                $val = '';
                while ($i < $len) {
                    if ($s[$i] === "'" && isset($s[$i + 1]) && $s[$i + 1] === "'") {
                        // Escaped single quote ''
                        $val .= "'";
                        $i   += 2;
                    } elseif ($s[$i] === "'") {
                        $i++; // closing quote
                        break;
                    } elseif ($s[$i] === '\\' && isset($s[$i + 1])) {
                        $val .= $s[$i + 1];
                        $i   += 2;
                    } else {
                        $val .= $s[$i];
                        $i++;
                    }
                }
                $result[] = $val;
            } elseif (strtolower(substr($s, $i, 4)) === 'null') {
                $result[] = null;
                $i        += 4;
            } elseif (strtolower(substr($s, $i, 4)) === 'true') {
                $result[] = 'true';
                $i        += 4;
            } elseif (strtolower(substr($s, $i, 5)) === 'false') {
                $result[] = 'false';
                $i        += 5;
            } else {
                // Bare token (number, date, keyword)
                $tok = '';
                while ($i < $len && $s[$i] !== ',' && $s[$i] !== ')') {
                    $tok .= $s[$i];
                    $i++;
                }
                $result[] = trim($tok) === '' ? null : trim($tok);
            }

            // Skip comma separator
            if ($i < $len && $s[$i] === ',') {
                $i++;
            }
        }

        return $result;
    }
}
