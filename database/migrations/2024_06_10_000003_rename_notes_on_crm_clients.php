<?php
// database/migrations/2024_06_10_000003_rename_notes_on_crm_clients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_clients', function (Blueprint $table) {
            $table->renameColumn('notes', 'initial_notes');
        });
    }

    public function down(): void
    {
        Schema::table('crm_clients', function (Blueprint $table) {
            $table->renameColumn('initial_notes', 'notes');
        });
    }
};
