<?php
// database/migrations/2026_08_06_000005_create_inventory_stock_movements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
            $table->string('movement_type'); // purchase_in, sale_out, exchange_out, exchange_in, adjustment_in, adjustment_out
            $table->decimal('quantity_grams', 14, 3); // always positive, direction implied by movement_type
            $table->decimal('unit_cost', 18, 4)->nullable(); // AED per gram at time of movement
            $table->decimal('total_cost', 18, 2)->nullable(); // AED, quantity_grams * unit_cost
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->dateTime('moved_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'inventory_item_id', 'moved_at'], 'inv_stock_movements_tenant_item_date_idx');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_movements');
    }
};
