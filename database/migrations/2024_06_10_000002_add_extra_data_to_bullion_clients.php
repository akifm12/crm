<?php
// database/migrations/2024_06_10_000002_add_extra_data_to_bullion_clients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->json('extra_data')->nullable()->after('screening_result');
        });
    }

    public function down(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->dropColumn('extra_data');
        });
    }
};
