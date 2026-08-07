<?php
// database/migrations/2026_08_06_000008_create_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bullion_client_id')->constrained()->restrictOnDelete();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_type'); // sale, purchase, exchange
            $table->string('status')->default('draft'); // draft, posted, void
            $table->date('invoice_date');
            $table->char('currency_code', 3)->default('AED');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('vat_total', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            // Soft link only (no FK constraint): client_transactions is a separate AML-facing
            // log table that may not exist/be migrated in every environment. This column just
            // records which row ClientTransactionSyncService created, if any.
            $table->unsignedBigInteger('client_transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('posted_at')->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
