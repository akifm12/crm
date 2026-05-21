<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('acronym', 20);
            $table->string('sector', 50);
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('website')->nullable();
            $table->string('jurisdiction', 100)->default('UAE');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('license_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sector', 50);
            $table->foreignId('suggested_regulator_id')->constrained('regulators')->cascadeOnDelete();
            $table->json('additional_regulator_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('compliance_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('regulator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_activity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('frequency', 30);
            $table->string('category', 60)->default('Reporting');
            $table->string('submission_channel')->nullable();
            $table->text('penalty_note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('compliance_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained('compliance_requirements')->cascadeOnDelete();
            $table->date('due_date');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->integer('year');
            $table->timestamps();

            $table->index(['requirement_id', 'year']);
            $table->index('due_date');
        });

        Schema::create('user_compliance_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('regulator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('notify_days_before')->default(7);
            $table->timestamps();

            $table->unique(['user_id', 'regulator_id', 'license_activity_id'], 'ucp_user_reg_act_unique');
        });

        Schema::create('user_fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('token');
            $table->string('platform', 10)->default('android');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_fcm_tokens');
        Schema::dropIfExists('user_compliance_profiles');
        Schema::dropIfExists('compliance_deadlines');
        Schema::dropIfExists('compliance_requirements');
        Schema::dropIfExists('license_activities');
        Schema::dropIfExists('regulators');
    }
};
