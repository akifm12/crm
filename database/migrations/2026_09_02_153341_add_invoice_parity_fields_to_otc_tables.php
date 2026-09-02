<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otc_deals', function (Blueprint $table) {
            $table->string('pricing_type')->default('fixed')->after('deal_type'); // fixed, unfixed
            $table->string('party_reference')->nullable()->after('deal_date');
            $table->decimal('premium_amount', 18, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 18, 2)->default(0)->after('premium_amount');
            $table->dateTime('fixed_at')->nullable()->after('posted_at');
            $table->foreignId('fixed_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('otc_deal_lines', function (Blueprint $table) {
            $table->string('line_type')->default('metal_out')->after('otc_deal_id'); // metal_in, metal_out, cash_topup, other
            $table->decimal('making_charge_rate', 18, 4)->nullable()->after('unit_price');
            $table->decimal('making_charge_amount', 18, 2)->default(0)->after('making_charge_rate');
            $table->decimal('line_total', 18, 2)->default(0)->after('line_subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('otc_deals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fixed_by');
            $table->dropColumn(['pricing_type', 'party_reference', 'premium_amount', 'discount_amount', 'fixed_at']);
        });

        Schema::table('otc_deal_lines', function (Blueprint $table) {
            $table->dropColumn(['line_type', 'making_charge_rate', 'making_charge_amount', 'line_total']);
        });
    }
};
