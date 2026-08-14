<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->boolean('monitoring_enabled')->default(false)->after('screening_result');
            $table->string('monitoring_frequency')->nullable()->after('monitoring_enabled'); // daily|weekly|monthly|quarterly|half_yearly|annually
            $table->timestamp('monitoring_last_screened_at')->nullable()->after('monitoring_frequency');
            $table->timestamp('monitoring_next_due_at')->nullable()->after('monitoring_last_screened_at');
        });
    }

    public function down(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->dropColumn(['monitoring_enabled', 'monitoring_frequency', 'monitoring_last_screened_at', 'monitoring_next_due_at']);
        });
    }
};
