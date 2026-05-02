<?php
// database/migrations/2024_06_01_000001_update_bullion_client_type_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bullion_clients MODIFY COLUMN client_type ENUM('corporate_local','corporate_import','corporate_export','individual') NOT NULL DEFAULT 'corporate_local'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bullion_clients MODIFY COLUMN client_type ENUM('individual','corporate') NOT NULL DEFAULT 'corporate'");
    }
};
