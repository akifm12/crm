<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standard_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('bullion_client_id')->nullable()->after('client_name');
            $table->foreign('bullion_client_id')->references('id')->on('bullion_clients')->nullOnDelete();
            $table->index('bullion_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('standard_invoices', function (Blueprint $table) {
            $table->dropForeign(['bullion_client_id']);
            $table->dropColumn('bullion_client_id');
        });
    }
};
