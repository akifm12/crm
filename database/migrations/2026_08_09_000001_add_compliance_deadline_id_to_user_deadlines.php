<?php
// database/migrations/2026_08_09_000001_add_compliance_deadline_id_to_user_deadlines.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_deadlines', function (Blueprint $table) {
            $table->foreignId('compliance_deadline_id')->nullable()->after('public_user_id')
                ->constrained('public_compliance_deadlines')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_deadlines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compliance_deadline_id');
        });
    }
};
