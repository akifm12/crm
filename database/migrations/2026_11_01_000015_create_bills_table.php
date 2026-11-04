<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('period_id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->string('bill_number', 50);
            $table->string('vendor_ref', 100)->nullable();
            $table->enum('bill_type', ['TAX_INVOICE','PROFORMA','CREDIT_NOTE','DEBIT_NOTE'])->default('TAX_INVOICE');
            $table->date('bill_date');
            $table->date('due_date');
            $table->enum('status', ['DRAFT','APPROVED','PAID','PARTIAL','VOID'])->default('DRAFT');
            $table->string('currency_code', 3)->default('AED');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->string('vendor_name');
            $table->string('vendor_trn', 50)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unique(['tenant_id', 'bill_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
