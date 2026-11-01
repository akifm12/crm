<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('period_id')->nullable();
            $table->string('entry_number', 30);
            $table->date('entry_date');
            $table->enum('journal_type', ['GENERAL','CASH_PAYMENT','BANK_PAYMENT','CASH_RECEIPT','BANK_RECEIPT','PURCHASE','SALE']);
            $table->enum('status', ['DRAFT','POSTED','VOID'])->default('DRAFT');
            $table->string('description');
            $table->string('reference', 100)->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->string('currency_code', 3)->default('AED');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->boolean('is_reversal')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'entry_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
