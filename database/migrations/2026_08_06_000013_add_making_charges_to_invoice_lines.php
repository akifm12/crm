<?php
// database/migrations/2026_08_06_000013_add_making_charges_to_invoice_lines.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Renamed so it's clearly paired with the new making_vat_* fields below — the
            // metal portion and the making-charge portion of a line can carry different
            // VAT treatments (e.g. metal zero-rated, making charges standard-rated).
            $table->renameColumn('vat_treatment', 'metal_vat_treatment');
            $table->renameColumn('vat_rate', 'metal_vat_rate');
            $table->renameColumn('vat_amount', 'metal_vat_amount');
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->decimal('purity', 6, 3)->nullable()->after('inventory_item_id');
            $table->decimal('gross_weight_grams', 14, 3)->nullable()->after('quantity_grams');
            $table->unsignedInteger('pcs')->nullable()->after('gross_weight_grams');
            $table->decimal('making_charge_rate', 18, 4)->nullable()->after('unit_price');
            $table->decimal('making_charge_amount', 18, 2)->default(0)->after('making_charge_rate');
            $table->string('making_vat_treatment')->default('standard')->after('making_charge_amount');
            $table->decimal('making_vat_rate', 5, 2)->default(0)->after('making_vat_treatment');
            $table->decimal('making_vat_amount', 18, 2)->default(0)->after('making_vat_rate');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropColumn([
                'purity', 'gross_weight_grams', 'pcs', 'making_charge_rate',
                'making_charge_amount', 'making_vat_treatment', 'making_vat_rate', 'making_vat_amount',
            ]);
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->renameColumn('metal_vat_treatment', 'vat_treatment');
            $table->renameColumn('metal_vat_rate', 'vat_rate');
            $table->renameColumn('metal_vat_amount', 'vat_amount');
        });
    }
};
