<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('bullion_client_id');
            $table->unsignedBigInteger('client_document_id')->nullable();
            $table->string('document_type_detected')->nullable();
            $table->json('raw_response')->nullable();
            $table->json('changes'); // [{model, model_id, field, old_value, new_value}]
            $table->enum('status', ['applied', 'reverted', 'no_changes', 'failed'])->default('applied');
            $table->string('failure_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['bullion_client_id', 'status']);
            $table->index('client_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_scan_logs');
    }
};
