<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_invoice_id');
            $table->string('description');
            $table->decimal('quantity', 15, 4)->default(1);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('amount', 15, 2);
            $table->string('vat_treatment')->default('standard');
            $table->decimal('vat_rate', 8, 4)->default(5);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->unsignedSmallInteger('line_order')->default(0);
            $table->timestamps();

            $table->foreign('standard_invoice_id')->references('id')->on('standard_invoices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_invoice_lines');
    }
};
