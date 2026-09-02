<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otc_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('reference')->nullable();
            $table->string('source_type')->nullable(); // 'otc_deal', 'otc_deal_payment', 'manual'
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('bullion_client_id')->nullable()->constrained()->nullOnDelete();
            $table->char('currency_code', 3)->default('AED');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->string('status')->default('posted'); // posted, void
            $table->foreignId('voided_journal_entry_id')->nullable()->constrained('otc_journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'entry_date']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otc_journal_entries');
    }
};
