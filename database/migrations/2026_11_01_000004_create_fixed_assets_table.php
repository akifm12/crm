<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('asset_number')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 18, 4);
            $table->decimal('salvage_value', 18, 4)->default(0);
            $table->decimal('useful_life_months', 8, 2)->default(60);
            $table->string('depreciation_method', 30)->default('straight_line');
            $table->decimal('accumulated_depreciation', 18, 4)->default(0);
            $table->decimal('net_book_value', 18, 4)->default(0);
            $table->enum('status', ['active', 'disposed', 'fully_depreciated'])->default('active');
            $table->date('last_depreciation_date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->foreign('category_id')->references('id')->on('asset_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
