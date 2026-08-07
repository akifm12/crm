<?php
// app/Services/Accounting/InvoiceNumberService.php

namespace App\Services\Accounting;

use App\Models\Tenant;
use App\Models\TenantInvoiceSequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    private const PREFIXES = [
        'sale'     => 'SL-',
        'purchase' => 'PU-',
        'exchange' => 'EX-',
    ];

    /**
     * Atomically mint the next invoice number for a tenant, in a sequence independent per
     * invoice type (sale/purchase/exchange each count from their own 1, with a recognizable
     * prefix) — this is the only place invoice numbers are minted, never derive from
     * max(invoice_number)+1 (gap/race bug, and UAE FTA expects gapless numbering). A voided
     * invoice keeps its number; numbers are never recycled.
     */
    public function next(Tenant $tenant, string $invoiceType): string
    {
        $this->ensureSequence($tenant, $invoiceType);

        return DB::transaction(function () use ($tenant, $invoiceType) {
            $sequence = TenantInvoiceSequence::where('tenant_id', $tenant->id)
                ->where('invoice_type', $invoiceType)
                ->lockForUpdate()
                ->first();

            $currentYear = (int) now()->format('Y');

            if ($sequence->year_reset && $sequence->current_year !== $currentYear) {
                $sequence->next_number = 1;
            }

            $number = $sequence->next_number;

            $sequence->update([
                'next_number'  => $number + 1,
                'current_year' => $currentYear,
            ]);

            $formatted = str_pad((string) $number, 5, '0', STR_PAD_LEFT);
            $yearPart = $sequence->year_reset ? $currentYear . '-' : '';

            return ($sequence->prefix ?? '') . $yearPart . $formatted;
        });
    }

    private function ensureSequence(Tenant $tenant, string $invoiceType): void
    {
        if (TenantInvoiceSequence::where('tenant_id', $tenant->id)->where('invoice_type', $invoiceType)->exists()) {
            return;
        }

        try {
            TenantInvoiceSequence::create([
                'tenant_id'    => $tenant->id,
                'invoice_type' => $invoiceType,
                'prefix'       => self::PREFIXES[$invoiceType] ?? 'INV-',
                'next_number'  => 1,
                'year_reset'   => false,
            ]);
        } catch (QueryException $e) {
            // Concurrent first-invoice race lost to another request — a sequence row exists either way.
        }
    }
}
