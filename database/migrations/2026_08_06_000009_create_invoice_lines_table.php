<?php
// database/migrations/2026_08_06_000009_create_invoice_lines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('line_type'); // metal_out, metal_in, cash_topup, other
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity_grams', 14, 3)->nullable();
            $table->decimal('unit_price', 18, 4)->nullable(); // per gram, invoice currency
            $table->decimal('line_subtotal', 18, 2)->default(0);
            $table->string('vat_treatment')->default('standard'); // standard, zero_rated, reverse_charge, exempt
            $table->decimal('vat_rate', 5, 2)->default(0); // numeric rate actually applied, stored per-line
            $table->decimal('vat_amount', 18, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
            $table->smallInteger('line_order')->default(0);
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
