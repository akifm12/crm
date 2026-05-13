<?php
// database/migrations/2024_06_13_000001_create_client_fill_tokens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_fill_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('client_name')->nullable();   // optional — pre-fill client name
            $table->string('client_email')->nullable();  // where to send the link
            $table->string('client_type')->default('individual'); // corporate or individual
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('bullion_client_id')->nullable()->constrained()->nullOnDelete(); // set after approval
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_fill_tokens');
    }
};
