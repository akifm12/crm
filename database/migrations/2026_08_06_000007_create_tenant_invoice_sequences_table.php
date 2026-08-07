<?php
// database/migrations/2026_08_06_000007_create_tenant_invoice_sequences_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('prefix')->nullable()->default('INV-');
            $table->unsignedInteger('next_number')->default(1);
            $table->boolean('year_reset')->default(false);
            $table->smallInteger('current_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invoice_sequences');
    }
};
