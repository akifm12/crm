<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('run_number', 30);
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->date('pay_date');
            $table->string('status', 20)->default('DRAFT'); // DRAFT, POSTED, VOID
            $table->decimal('total_basic_salary', 20, 4)->default(0);
            $table->decimal('total_allowances', 20, 4)->default(0);
            $table->decimal('total_deductions', 20, 4)->default(0);
            $table->decimal('total_net_pay', 20, 4)->default(0);
            $table->integer('employee_count')->default(0);
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->boolean('wps_file_generated')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'run_number']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
