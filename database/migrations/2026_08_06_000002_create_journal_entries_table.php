<?php
// database/migrations/2026_08_06_000002_create_journal_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('reference')->nullable();
            $table->string('source_type')->nullable(); // e.g. 'invoice', 'manual', 'stock_adjustment' — lightweight tag, no hard FK
            $table->unsignedBigInteger('source_id')->nullable();
            $table->char('currency_code', 3)->default('AED');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->string('status')->default('posted'); // posted, void — never edited/deleted, corrected via reversing entry
            $table->foreignId('voided_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'entry_date']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
