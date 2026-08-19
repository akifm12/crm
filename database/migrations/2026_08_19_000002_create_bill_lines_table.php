<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('category')->default('other');
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('vat_treatment')->default('standard');
            $table->decimal('vat_rate', 8, 4)->default(0);
            $table->decimal('vat_amount', 18, 2)->default(0);
            $table->decimal('line_total', 18, 2)->default(0);
            $table->integer('line_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_lines');
    }
};
