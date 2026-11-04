<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_run_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('tenant_id');
            $table->decimal('basic_salary', 20, 4);
            $table->decimal('housing_allowance', 20, 4)->default(0);
            $table->decimal('transport_allowance', 20, 4)->default(0);
            $table->decimal('other_allowances', 20, 4)->default(0);
            $table->decimal('overtime_amount', 20, 4)->default(0);
            $table->decimal('gross_pay', 20, 4);
            $table->decimal('loan_deduction', 20, 4)->default(0);
            $table->decimal('absence_deduction', 20, 4)->default(0);
            $table->decimal('other_deductions', 20, 4)->default(0);
            $table->decimal('total_deductions', 20, 4)->default(0);
            $table->decimal('net_pay', 20, 4);
            $table->decimal('days_worked', 6, 2)->nullable();
            $table->decimal('days_absent', 6, 2)->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);
            $table->decimal('eosb_accrual', 20, 4)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['payroll_run_id']);
            $table->index(['employee_id']);
            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_lines');
    }
};
