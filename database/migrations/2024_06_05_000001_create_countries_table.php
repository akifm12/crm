<?php
// database/migrations/2024_06_05_000001_create_countries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->string('country_code', 2)->primary();
            $table->string('country_name', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
