<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained('crm_clients')->cascadeOnDelete();
            $table->string('employee_name');
            $table->string('employee_role')->nullable();
            $table->string('training_type');           // AML, CFT, Compliance, DPMSR, etc.
            $table->date('training_date');
            $table->date('expiry_date')->nullable();
            $table->string('trainer')->nullable();
            $table->text('notes')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('material_path')->nullable();
            $table->enum('status', ['completed', 'pending', 'expired'])->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_employee_trainings');
    }
};
