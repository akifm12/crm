<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('run_number')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date');
            $table->decimal('total_gross', 18, 4)->default(0);
            $table->decimal('total_deductions', 18, 4)->default(0);
            $table->decimal('total_net', 18, 4)->default(0);
            $table->enum('status', ['draft', 'posted', 'void'])->default('draft');
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'period_start']);
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_run_id');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('basic_salary', 18, 4)->default(0);
            $table->decimal('allowances', 18, 4)->default(0);
            $table->decimal('deductions', 18, 4)->default(0);
            $table->decimal('net_pay', 18, 4)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('payroll_run_id')->references('id')->on('payroll_runs')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
    }
};
