<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\ClientFillToken;
use App\Models\ClientShareholder;
use App\Models\ClientSignatory;
use App\Models\ClientTransaction;
use App\Models\ClientUbo;
use App\Models\DocumentScanLog;
use App\Models\GoamlReport;
use App\Models\InventoryBalance;
use App\Models\InventoryItem;
use App\Models\InventoryStockMovement;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ScreeningLog;
use App\Models\StandardInvoice;
use App\Models\StandardInvoiceLine;
use App\Models\StandardInvoicePayment;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WipeTenantData extends Command
{
    // Single tenant only, by design — this command has no "all tenants" mode, so
    // there is no way to accidentally wipe more than the one slug given.
    protected $signature = 'tenant:wipe-data
        {slug : The exact tenant slug to wipe — no other tenant is ever touched}
        {--dry-run : Preview counts without deleting anything}';

    protected $description = 'Permanently delete all clients/KYC, accounting, inventory, and goAML data for one tenant — keeps the tenant record, its settings/modules, chart of accounts, and users';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $tenant = Tenant::where('slug', $this->argument('slug'))->first();
        if (! $tenant) {
            $this->error("Tenant not found: {$this->argument('slug')}");
            return self::FAILURE;
        }

        $tid = $tenant->id;

        $clientIds     = BullionClient::where('tenant_id', $tid)->pluck('id');
        $invoiceIds    = Invoice::where('tenant_id', $tid)->pluck('id');
        $stdInvoiceIds = StandardInvoice::where('tenant_id', $tid)->pluck('id');
        $journalIds    = JournalEntry::where('tenant_id', $tid)->pluck('id');

        $counts = [
            'Clients (KYC records)'      => $clientIds->count(),
            'Client documents'           => ClientDocument::where('tenant_id', $tid)->count(),
            'Shareholders'                => ClientShareholder::whereIn('bullion_client_id', $clientIds)->count(),
            'Signatories'                 => ClientSignatory::whereIn('bullion_client_id', $clientIds)->count(),
            'UBOs'                        => ClientUbo::whereIn('bullion_client_id', $clientIds)->count(),
            'Manual transaction log rows' => ClientTransaction::where('tenant_id', $tid)->count(),
            'Screening logs'              => ScreeningLog::where('tenant_id', $tid)->count(),
            'Self-fill KYC links'         => ClientFillToken::where('tenant_id', $tid)->count(),
            'goAML reports'               => GoamlReport::where('tenant_id', $tid)->count(),
            'Bullion invoices'            => $invoiceIds->count(),
            'Bullion invoice payments'    => InvoicePayment::where('tenant_id', $tid)->count(),
            'Standard invoices (RE/general)' => $stdInvoiceIds->count(),
            'Standard invoice payments'   => StandardInvoicePayment::where('tenant_id', $tid)->count(),
            'Bills'                       => Bill::where('tenant_id', $tid)->count(),
            'Suppliers'                   => Supplier::where('tenant_id', $tid)->count(),
            'Inventory items'             => InventoryItem::where('tenant_id', $tid)->count(),
            'Journal entries'             => $journalIds->count(),
        ];

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Wiping data for tenant: {$tenant->name}  (slug: {$tenant->slug}, id: {$tid})");
        $this->newLine();
        $this->line('Would permanently delete:');
        foreach ($counts as $label => $n) {
            $this->line(sprintf('  %-32s %d', $label, $n));
        }
        $this->newLine();
        $this->comment('KEPT untouched: the tenant record itself, its settings/enabled modules, chart of accounts, and staff user logins.');
        $this->comment('No other tenant is touched by this command — every query above is scoped to this tenant only.');

        if (array_sum($counts) === 0) {
            $this->newLine();
            $this->info('Nothing to wipe — this tenant already has no data.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('Dry run only — nothing was deleted. Re-run without --dry-run to commit.');
            return self::SUCCESS;
        }

        $this->newLine();
        if (! $this->confirm("Type yes to PERMANENTLY delete everything listed above for '{$tenant->slug}'. This cannot be undone.", false)) {
            $this->comment('Cancelled — nothing was deleted.');
            return self::SUCCESS;
        }

        // Collect file paths that need cleaning up from disk before their DB rows go.
        $billAttachments = Bill::where('tenant_id', $tid)->whereNotNull('attachment_path')->pluck('attachment_path');
        $goamlFiles       = GoamlReport::where('tenant_id', $tid)->whereNotNull('xml_file_path')->pluck('xml_file_path');

        DB::transaction(function () use ($tid, $clientIds, $invoiceIds, $stdInvoiceIds, $journalIds) {
            DocumentScanLog::whereIn('bullion_client_id', $clientIds)->delete();
            ClientDocument::where('tenant_id', $tid)->delete();
            ClientShareholder::whereIn('bullion_client_id', $clientIds)->delete();
            ClientSignatory::whereIn('bullion_client_id', $clientIds)->delete();
            ClientUbo::whereIn('bullion_client_id', $clientIds)->delete();
            ClientTransaction::where('tenant_id', $tid)->delete();
            ScreeningLog::where('tenant_id', $tid)->delete();
            ClientFillToken::where('tenant_id', $tid)->delete();
            GoamlReport::where('tenant_id', $tid)->delete();

            InvoiceLine::whereIn('invoice_id', $invoiceIds)->delete();
            InvoicePayment::where('tenant_id', $tid)->delete();
            Invoice::where('tenant_id', $tid)->delete();

            StandardInvoiceLine::whereIn('standard_invoice_id', $stdInvoiceIds)->delete();
            StandardInvoicePayment::where('tenant_id', $tid)->delete();
            StandardInvoice::where('tenant_id', $tid)->delete();

            Bill::where('tenant_id', $tid)->delete();
            Supplier::where('tenant_id', $tid)->delete();

            InventoryStockMovement::where('tenant_id', $tid)->delete();
            InventoryBalance::where('tenant_id', $tid)->delete();
            InventoryItem::where('tenant_id', $tid)->delete();

            JournalEntryLine::whereIn('journal_entry_id', $journalIds)->delete();
            JournalEntry::where('tenant_id', $tid)->delete();

            // Deleted last — every FK above pointed at bullion_client_id, not the other way round.
            BullionClient::where('tenant_id', $tid)->delete();
        });

        // Best-effort file cleanup on disk, outside the DB transaction.
        Storage::disk('local')->deleteDirectory("tenants/{$tid}/clients");
        foreach ($billAttachments as $path) {
            Storage::disk('local')->delete($path);
        }
        foreach ($goamlFiles as $path) {
            Storage::disk('local')->delete($path);
        }

        $this->newLine();
        $this->info("Done — all data wiped for '{$tenant->slug}'. Tenant, settings, chart of accounts, and users preserved.");

        return self::SUCCESS;
    }
}
