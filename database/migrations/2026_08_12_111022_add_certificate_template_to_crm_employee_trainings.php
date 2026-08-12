<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_employee_trainings', function (Blueprint $table) {
            $table->tinyInteger('certificate_template')->default(1)->after('status');
            $table->string('signatory_name')->nullable()->after('certificate_template');
            $table->string('signatory_title')->nullable()->after('signatory_name');
        });
    }

    public function down(): void
    {
        Schema::table('crm_employee_trainings', function (Blueprint $table) {
            $table->dropColumn(['certificate_template', 'signatory_name', 'signatory_title']);
        });
    }
};
