<?php
// database/migrations/2026_08_07_000002_add_metal_rates_to_invoices.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Replaces the single gold_rate_per_oz/gold_rate_per_gram/usd_aed_rate triple for
            // invoices that mix metals — one entry per metal used, e.g.
            // {"gold": {"usd_per_oz": 4050, "usd_aed_rate": 3.674}, "silver": {...}}.
            // Old columns are kept (unused by new code) rather than migrated — only one real
            // invoice has gold-rate data and a data migration for a single row isn't worth it.
            $table->json('metal_rates')->nullable()->after('gold_rate_per_gram');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('metal_rates');
        });
    }
};
