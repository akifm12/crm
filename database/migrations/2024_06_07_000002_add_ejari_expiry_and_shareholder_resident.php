<?php
// database/migrations/2024_06_07_000002_add_ejari_expiry_and_shareholder_resident.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add ejari_expiry to bullion_clients
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->date('ejari_expiry')->nullable()->after('ejari_number');
        });

        // Add resident fields to client_shareholders
        Schema::table('client_shareholders', function (Blueprint $table) {
            $table->boolean('is_resident')->default(false)->after('is_ubo');
            $table->string('eid_number')->nullable()->after('is_resident');
            $table->date('eid_expiry')->nullable()->after('eid_number');
        });
    }

    public function down(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->dropColumn('ejari_expiry');
        });
        Schema::table('client_shareholders', function (Blueprint $table) {
            $table->dropColumn(['is_resident', 'eid_number', 'eid_expiry']);
        });
    }
};
