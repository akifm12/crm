<?php
// database/migrations/2026_08_08_000002_create_public_accounts_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('subscribed_to_updates')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['trade_license', 'ejari', 'passport', 'eid', 'dnfbp_registration', 'pi_insurance', 'regulatory_license', 'other']);
            $table->string('label')->nullable();
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_deadlines');
        Schema::dropIfExists('public_users');
    }
};
