<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Some clients arrive with their identity already verified by a third
    // party the tenant genuinely has no way to get documents from -- e.g. a
    // marketplace (Noon.com, Amazon.ae) that owns the buyer relationship and
    // performs its own KYC, handing the tenant only a name and order
    // reference. This isn't the tenant skipping KYC; it's recording who
    // actually did it, so passport/EID can be waived for exactly this case
    // instead of blocking the record entirely.
    public function up(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->string('kyc_source')->default('direct')->after('status'); // direct, third_party
            $table->string('kyc_source_platform')->nullable()->after('kyc_source');
            $table->string('kyc_source_reference')->nullable()->after('kyc_source_platform');
        });
    }

    public function down(): void
    {
        Schema::table('bullion_clients', function (Blueprint $table) {
            $table->dropColumn(['kyc_source', 'kyc_source_platform', 'kyc_source_reference']);
        });
    }
};
