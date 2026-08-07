<?php
// database/migrations/2026_08_06_000003_create_journal_entry_lines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')->constrained()->restrictOnDelete();
            $table->decimal('debit', 18, 2)->default(0);  // entry currency
            $table->decimal('credit', 18, 2)->default(0); // entry currency
            $table->decimal('base_debit', 18, 2)->default(0);  // AED, = debit * exchange_rate
            $table->decimal('base_credit', 18, 2)->default(0); // AED, = credit * exchange_rate
            $table->string('description')->nullable();
            $table->smallInteger('line_order')->default(0);
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('chart_of_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
