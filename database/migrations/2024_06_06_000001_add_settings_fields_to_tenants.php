<?php
// database/migrations/2024_06_06_000001_add_settings_fields_to_tenants.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('contact_email');
            $table->string('address')->nullable()->after('phone');
            $table->string('trade_license_no')->nullable()->after('address');
            $table->string('dnfbp_reg_no')->nullable()->after('trade_license_no');
            $table->string('mlro_name')->nullable()->after('dnfbp_reg_no');
            $table->string('mlro_email')->nullable()->after('mlro_name');
            $table->string('mlro_phone')->nullable()->after('mlro_email');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['phone','address','trade_license_no','dnfbp_reg_no','mlro_name','mlro_email','mlro_phone']);
        });
    }
};
