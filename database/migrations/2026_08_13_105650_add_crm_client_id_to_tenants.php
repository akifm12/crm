<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('crm_client_id')->nullable()->after('id');
            $table->foreign('crm_client_id')->references('id')->on('crm_clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['crm_client_id']);
            $table->dropColumn('crm_client_id');
        });
    }
};
