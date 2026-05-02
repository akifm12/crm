<?php
// database/migrations/2024_06_05_000003_add_dob_to_signatories_and_shareholders.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_signatories', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('nationality');
        });

        Schema::table('client_shareholders', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('nationality');
        });
    }

    public function down(): void
    {
        Schema::table('client_signatories', function (Blueprint $table) {
            $table->dropColumn('dob');
        });
        Schema::table('client_shareholders', function (Blueprint $table) {
            $table->dropColumn('dob');
        });
    }
};
