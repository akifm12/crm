<?php
// database/migrations/2026_08_07_000005_add_bullion_client_id_to_journal_entries.php

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('bullion_client_id')->nullable()->after('source_id')
                ->constrained('bullion_clients')->nullOnDelete();
        });

        // Backfill existing entries using the same indirect lookup ReportingService::
        // clientStatement() already relies on, so historical data isn't orphaned in the
        // new client-breakdown report — going forward, LedgerService::post() sets this
        // directly instead of requiring these joins.
        $invoiceClientMap = Invoice::pluck('bullion_client_id', 'id');

        JournalEntry::whereIn('source_type', ['invoice', 'deposit_reclass'])
            ->whereNotNull('source_id')
            ->get(['id', 'source_id'])
            ->each(function (JournalEntry $entry) use ($invoiceClientMap) {
                $clientId = $invoiceClientMap->get($entry->source_id);
                if ($clientId) {
                    DB::table('journal_entries')->where('id', $entry->id)->update(['bullion_client_id' => $clientId]);
                }
            });

        InvoicePayment::whereNotNull('journal_entry_id')->get(['journal_entry_id', 'bullion_client_id'])
            ->each(function (InvoicePayment $payment) {
                if ($payment->bullion_client_id) {
                    DB::table('journal_entries')->where('id', $payment->journal_entry_id)
                        ->whereNull('bullion_client_id')
                        ->update(['bullion_client_id' => $payment->bullion_client_id]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropForeign(['bullion_client_id']);
            $table->dropColumn('bullion_client_id');
        });
    }
};
