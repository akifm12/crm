<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->string('invoice_code', 10)->nullable()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->dropColumn('invoice_code');
        });
    }
};
