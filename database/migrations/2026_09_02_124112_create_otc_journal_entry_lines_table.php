<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otc_journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otc_journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('otc_chart_of_account_id')->constrained()->restrictOnDelete();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->decimal('base_debit', 18, 2)->default(0);
            $table->decimal('base_credit', 18, 2)->default(0);
            $table->string('description')->nullable();
            $table->smallInteger('line_order')->default(0);
            $table->timestamps();

            $table->index('otc_journal_entry_id');
            $table->index('otc_chart_of_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otc_journal_entry_lines');
    }
};
