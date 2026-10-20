<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->text('archive_reason')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->dropColumn(['archive_reason', 'archived_by']);
        });
    }
};
