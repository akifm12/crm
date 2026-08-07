<?php
// database/migrations/2026_08_06_000006_create_inventory_balances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_grams', 14, 3)->default(0);
            $table->decimal('weighted_avg_cost', 18, 4)->default(0); // AED per gram
            $table->decimal('total_value', 18, 2)->default(0); // AED, quantity_grams * weighted_avg_cost
            $table->timestamp('updated_at')->nullable();

            $table->unique('inventory_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};
