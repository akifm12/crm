<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_invoice_id');
            $table->unsignedBigInteger('tenant_id');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('standard_invoice_id')->references('id')->on('standard_invoices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_invoice_payments');
    }
};
