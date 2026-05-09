<?php
// database/migrations/2024_06_10_000001_expand_client_type_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bullion_clients MODIFY client_type ENUM(
            'corporate_local',
            'corporate_import',
            'corporate_export',
            'corporate',
            'corporate_foreign',
            'buyer',
            'seller',
            'developer',
            'landlord',
            'tenant_client',
            'individual'
        ) NOT NULL DEFAULT 'corporate_local'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bullion_clients MODIFY client_type ENUM(
            'corporate_local','corporate_import','corporate_export','individual'
        ) NOT NULL DEFAULT 'corporate_local'");
    }
};
