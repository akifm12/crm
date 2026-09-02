<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otc_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            // Optional link when the OTC counterparty is also an A-Book client — lets B-Book
            // show a consolidated view, without requiring the counterparty to be onboarded/screened.
            $table->foreignId('bullion_client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deal_number');
            $table->string('deal_type'); // buy, sell, exchange
            $table->string('counterparty_name');
            $table->date('deal_date');
            $table->string('status')->default('draft'); // draft, posted, void
            $table->char('currency_code', 3)->default('AED');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->json('metal_rates')->nullable(); // {gold: {usd_per_oz, usd_aed_rate}, ...}
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0); // no VAT — OTC deals are generic, not tax invoices
            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('otc_journal_entry_id')->nullable()->constrained('otc_journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'deal_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otc_deals');
    }
};
