<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 20);
            $table->string('name');
            $table->string('name_arabic')->nullable();
            $table->string('description')->nullable();
            $table->enum('account_type', ['ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE']);
            $table->string('account_sub_type', 50)->nullable();
            $table->enum('normal_balance', ['DEBIT','CREDIT']);
            $table->boolean('is_control')->default(false);
            $table->boolean('is_bank')->default(false);
            $table->boolean('is_cash')->default(false);
            $table->boolean('is_receivable')->default(false);
            $table->boolean('is_payable')->default(false);
            $table->boolean('is_vat_account')->default(false);
            $table->string('currency_code', 3)->default('AED');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->decimal('opening_balance', 15, 2)->nullable();
            $table->date('opening_balance_date')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
