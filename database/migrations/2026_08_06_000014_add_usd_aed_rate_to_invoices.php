<?php
// database/migrations/2026_08_06_000014_add_usd_aed_rate_to_invoices.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // gold_rate_per_oz is the USD spot price; this is what converts it to
            // gold_rate_per_gram (AED): (gold_rate_per_oz / 31.1035) * usd_aed_rate.
            $table->decimal('usd_aed_rate', 9, 4)->default(3.674)->after('gold_rate_per_gram');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('usd_aed_rate');
        });
    }
};
