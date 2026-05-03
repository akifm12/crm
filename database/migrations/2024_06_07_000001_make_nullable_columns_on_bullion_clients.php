<?php
// database/migrations/2024_06_07_000001_make_nullable_columns_on_bullion_clients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->string('company_name')->nullable()->change();
            $table->string('business_activity')->nullable()->change();
            $table->string('country_of_incorporation')->nullable()->change();
            $table->string('full_name')->nullable()->change();
            $table->string('nationality')->nullable()->change();
        });
    }

    public function down(): void {}
};
