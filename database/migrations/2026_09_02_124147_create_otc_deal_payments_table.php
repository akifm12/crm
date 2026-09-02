<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otc_deal_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otc_deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('direction')->default('in'); // in = receipt, out = payment
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('otc_journal_entry_id')->nullable()->constrained('otc_journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otc_deal_payments');
    }
};
