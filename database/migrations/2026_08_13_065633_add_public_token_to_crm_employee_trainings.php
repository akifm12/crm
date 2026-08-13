<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_employee_trainings', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('signatory_title');
        });

        // Backfill existing records
        \App\Models\CrmEmployeeTraining::whereNull('public_token')->each(function ($t) {
            $t->update(['public_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('crm_employee_trainings', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
