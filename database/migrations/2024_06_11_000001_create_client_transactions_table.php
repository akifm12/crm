<?php
// database/migrations/2024_06_11_000001_create_client_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bullion_client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->decimal('invoice_amount', 15, 2)->nullable();
            $table->string('transaction_type')->nullable(); // buy, sell, exchange
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_transactions');
    }
};
