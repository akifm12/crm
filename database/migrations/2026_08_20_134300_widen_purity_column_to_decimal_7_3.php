<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen purity from decimal(6,3) to decimal(7,3) to accommodate 1000.000 (fine gold)
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('purity', 7, 3)->nullable()->change();
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->decimal('purity', 7, 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->decimal('purity', 6, 3)->nullable()->change();
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->decimal('purity', 6, 3)->nullable()->change();
        });
    }
};
