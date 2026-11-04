<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('asset_number', 30);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('assigned_to', 255)->nullable();
            $table->date('purchase_date');
            $table->date('in_service_date');
            $table->date('disposal_date')->nullable();
            $table->decimal('purchase_cost', 20, 4);
            $table->decimal('residual_value', 20, 4)->default(0);
            $table->decimal('depreciable_amount', 20, 4);
            $table->string('depreciation_method', 30)->default('straight_line');
            $table->decimal('useful_life_years', 6, 2);
            $table->decimal('depreciation_rate', 8, 6)->nullable();
            $table->decimal('accumulated_depreciation', 20, 4)->default(0);
            $table->decimal('net_book_value', 20, 4);
            $table->unsignedBigInteger('asset_account_id');
            $table->unsignedBigInteger('accum_depr_account_id');
            $table->unsignedBigInteger('depr_expense_account_id');
            $table->string('status', 20)->default('active'); // active, disposed, fully_depreciated
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('purchase_invoice_ref', 100)->nullable();
            $table->string('insurance_policy_no', 100)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'asset_number']);
            $table->index('tenant_id');
            $table->foreign('category_id')->references('id')->on('asset_categories')->nullOnDelete();
            $table->foreign('asset_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('accum_depr_account_id')->references('id')->on('chart_of_accounts');
            $table->foreign('depr_expense_account_id')->references('id')->on('chart_of_accounts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
