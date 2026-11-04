<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('employee_number')->nullable();
            $table->string('full_name');
            $table->string('position')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->decimal('basic_salary', 18, 4)->default(0);
            $table->date('join_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
