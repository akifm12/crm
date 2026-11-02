<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('depreciation_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('asset_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('depreciation_amount', 20, 4);
            $table->decimal('accumulated_depreciation', 20, 4);
            $table->decimal('net_book_value', 20, 4);
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->foreign('asset_id')->references('id')->on('fixed_assets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depreciation_lines');
    }
};
