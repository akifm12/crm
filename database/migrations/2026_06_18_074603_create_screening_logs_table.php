<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bullion_client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('screened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('query');
            $table->string('entity_type', 20)->default('entity');
            $table->string('status', 20)->default('clear');
            $table->unsignedInteger('total_hits')->default(0);
            $table->string('source', 20)->default('adhoc');
            $table->string('reference')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_logs');
    }
};
