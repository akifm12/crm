<?php
// database/migrations/2024_06_02_000001_add_risk_assessment_data_to_bullion_clients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->json('risk_assessment_data')->nullable()->after('risk_notes');
            $table->timestamp('risk_assessed_at')->nullable()->after('risk_assessment_data');
            $table->unsignedBigInteger('risk_assessed_by')->nullable()->after('risk_assessed_at');
        });
    }

    public function down(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->dropColumn(['risk_assessment_data', 'risk_assessed_at', 'risk_assessed_by']);
        });
    }
};
