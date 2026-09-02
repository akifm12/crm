<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otc_deal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otc_deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('metal_type')->nullable(); // gold, silver, platinum, palladium
            $table->string('description')->nullable();
            $table->decimal('purity', 7, 3)->nullable();
            $table->decimal('gross_weight_grams', 14, 4)->nullable();
            $table->decimal('quantity_grams', 14, 4)->nullable(); // pure weight = gross × purity/1000
            $table->integer('pcs')->nullable();
            $table->decimal('unit_price', 18, 6)->nullable(); // AED per gram
            $table->decimal('line_subtotal', 18, 2)->default(0);
            $table->smallInteger('line_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otc_deal_lines');
    }
};
