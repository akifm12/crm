<?php
// database/migrations/2026_08_07_000001_add_metal_type_to_inventory_items.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('metal_type')->default('gold')->after('name'); // gold, silver, platinum, palladium
            $table->decimal('nominal_weight_grams', 14, 4)->nullable()->after('purity'); // e.g. a TT Bar item = 116.638g
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['metal_type', 'nominal_weight_grams']);
        });
    }
};
