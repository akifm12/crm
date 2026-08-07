<?php
// database/migrations/2026_08_07_000004_add_pieces_to_inventory_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_stock_movements', function (Blueprint $table) {
            $table->unsignedInteger('pieces')->nullable()->after('quantity_grams');
        });

        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->unsignedInteger('pieces')->nullable()->after('quantity_grams');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_stock_movements', function (Blueprint $table) {
            $table->dropColumn('pieces');
        });

        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->dropColumn('pieces');
        });
    }
};
