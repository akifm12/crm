<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_account_id');
            $table->unsignedBigInteger('tenant_id');
            $table->date('transaction_date');
            $table->string('description');
            $table->decimal('amount', 18, 4);
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
            $table->index('tenant_id');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
