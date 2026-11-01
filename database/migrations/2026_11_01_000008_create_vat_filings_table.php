<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_filings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->date('period_from');
            $table->date('period_to');
            $table->date('due_date')->nullable();
            $table->decimal('standard_rated_sales', 18, 4)->default(0);
            $table->decimal('vat_on_sales', 18, 4)->default(0);
            $table->decimal('standard_rated_purchases', 18, 4)->default(0);
            $table->decimal('vat_on_purchases', 18, 4)->default(0);
            $table->decimal('net_vat_payable', 18, 4)->default(0);
            $table->json('box_values')->nullable();
            $table->enum('status', ['draft', 'submitted', 'paid'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'period_from']);
            $table->unique(['tenant_id', 'period_from', 'period_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_filings');
    }
};
