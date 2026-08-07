<?php
// database/migrations/2026_08_06_000012_add_pricing_fields_to_invoices.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // fixed | unfixed — unfixed invoices move inventory at posting but defer the
            // monetary journal entry until a later Fix Price action (sale invoices only).
            $table->string('pricing_type')->default('fixed');
            $table->decimal('gold_rate_per_oz', 18, 4)->nullable();
            $table->decimal('gold_rate_per_gram', 18, 6)->nullable();
            $table->string('party_reference')->nullable();
            $table->decimal('premium_amount', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->dateTime('fixed_at')->nullable();
            $table->foreignId('fixed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fixed_by');
            $table->dropColumn([
                'pricing_type', 'gold_rate_per_oz', 'gold_rate_per_gram',
                'party_reference', 'premium_amount', 'discount_amount', 'fixed_at',
            ]);
        });
    }
};
