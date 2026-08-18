<?php

namespace App\Console\Commands;

use App\Models\BullionClient;
use App\Models\ClientTransaction;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InventoryStockMovement;
use App\Models\JournalEntry;
use App\Models\Tenant;
use App\Services\Accounting\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetClientAccounting extends Command
{
    protected $signature = 'client:reset-accounting
                            {client : Client ID or exact display name}
                            {--tenant= : Tenant slug (required if client name is not unique)}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Delete all invoices, payments, and journal entries for a test client (dev use only). Reverses inventory movements.';

    public function __construct(private readonly InventoryService $inventory)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $client = $this->resolveClient();
        if (! $client) {
            return self::FAILURE;
        }

        $invoiceIds = Invoice::where('bullion_client_id', $client->id)->pluck('id');

        $counts = [
            'invoices'            => $invoiceIds->count(),
            'payments'            => InvoicePayment::where('bullion_client_id', $client->id)->count(),
            'journal_entries'     => JournalEntry::where('bullion_client_id', $client->id)->count(),
            'stock_movements'     => InventoryStockMovement::where('source_type', 'invoice')->whereIn('source_id', $invoiceIds)->count(),
            'client_transactions' => ClientTransaction::where('bullion_client_id', $client->id)->count(),
        ];

        $this->warn('');
        $this->warn('  CLIENT ACCOUNTING RESET');
        $this->line('  Client : ' . $client->displayName() . ' (ID ' . $client->id . ')');
        $this->line('  Tenant : ' . $client->tenant->name . ' (' . $client->tenant->slug . ')');
        $this->warn('');
        $this->line('  This will permanently delete:');
        $this->line('    ' . $counts['invoices']            . ' invoice(s) + lines');
        $this->line('    ' . $counts['payments']            . ' payment record(s)');
        $this->line('    ' . $counts['journal_entries']     . ' journal entr(y/ies) + lines');
        $this->line('    ' . $counts['stock_movements']     . ' inventory movement(s) — reversed first');
        $this->line('    ' . $counts['client_transactions'] . ' AML transaction record(s)');
        $this->warn('');
        $this->warn('  The client record itself is NOT deleted.');
        $this->warn('');

        if (array_sum($counts) === 0) {
            $this->info('  Nothing to delete — this client has no accounting data.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('  Proceed?', false)) {
            $this->line('  Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($client, $invoiceIds) {
            // 1. Reverse (then delete) inventory movements so stock balances are restored.
            $movements = InventoryStockMovement::where('source_type', 'invoice')
                ->whereIn('source_id', $invoiceIds)
                ->get();

            foreach ($movements as $movement) {
                $this->inventory->reverseMovement($movement);
            }
            InventoryStockMovement::where('source_type', 'invoice')
                ->whereIn('source_id', $invoiceIds)
                ->delete();

            // 2. Delete journal entries for this client (cascades to journal_entry_lines;
            //    nullifies invoice.journal_entry_id and payment.journal_entry_id via DB FKs).
            JournalEntry::where('bullion_client_id', $client->id)->delete();

            // 3. Delete payments (including standalone deposits).
            InvoicePayment::where('bullion_client_id', $client->id)->delete();

            // 4. Delete invoices (cascades to invoice_lines).
            Invoice::where('bullion_client_id', $client->id)->delete();

            // 5. Delete AML transaction log records.
            ClientTransaction::where('bullion_client_id', $client->id)->delete();
        });

        $this->info('  Done. All accounting data cleared for ' . $client->displayName() . '.');
        return self::SUCCESS;
    }

    private function resolveClient(): ?BullionClient
    {
        $arg = $this->argument('client');
        $tenantSlug = $this->option('tenant');

        $query = BullionClient::with('tenant');

        if ($tenantSlug) {
            $tenant = Tenant::where('slug', $tenantSlug)->first();
            if (! $tenant) {
                $this->error("Tenant '{$tenantSlug}' not found.");
                return null;
            }
            $query->where('tenant_id', $tenant->id);
        }

        // Try by ID first, then by full/company name
        $client = is_numeric($arg)
            ? $query->find((int) $arg)
            : $query->where(function ($q) use ($arg) {
                $q->where('full_name', $arg)->orWhere('company_name', $arg);
            })->first();

        if (! $client) {
            $this->error("Client '{$arg}' not found" . ($tenantSlug ? " in tenant '{$tenantSlug}'" : '') . '.');
            $this->line('Tip: use the client ID from the URL, or pass --tenant= to narrow by tenant slug.');
            return null;
        }

        return $client;
    }
}
