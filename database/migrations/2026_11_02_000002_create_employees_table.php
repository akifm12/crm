<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('employee_id', 30);
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('full_name', 255);
            $table->string('nationality', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('emirates_id', 20)->nullable();
            $table->string('passport_no', 20)->nullable();
            $table->string('visa_no', 30)->nullable();
            $table->date('visa_expiry')->nullable();
            $table->string('labour_card_no', 30)->nullable();
            $table->string('designation', 100)->nullable();
            $table->date('join_date');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->decimal('basic_salary', 20, 4);
            $table->decimal('housing_allowance', 20, 4)->default(0);
            $table->decimal('transport_allowance', 20, 4)->default(0);
            $table->decimal('other_allowances', 20, 4)->default(0);
            $table->decimal('gross_salary', 20, 4);
            $table->string('payment_frequency', 20)->default('monthly');
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('wps_routing_code', 20)->nullable();
            $table->unsignedBigInteger('salary_account_id')->nullable();
            $table->integer('annual_leave_days')->default(30);
            $table->integer('sick_leave_days')->default(15);
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'employee_id']);
            $table->index('tenant_id');
            $table->index('department_id');
        });

        // Add manager FK to departments after employees exists
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
        Schema::dropIfExists('employees');
    }
};
