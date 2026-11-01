<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 30);
            $table->string('name');
            $table->string('name_arabic')->nullable();
            $table->string('trn', 50)->nullable();
            $table->boolean('is_vat_registered')->default(false);
            $table->boolean('is_foreign_vendor')->default(false);
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('emirate', 50)->nullable();
            $table->string('country', 50)->default('UAE');
            $table->unsignedInteger('payment_terms_days')->default(30);
            $table->string('currency_code', 3)->default('AED');
            $table->unsignedBigInteger('ap_account_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('swift_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
