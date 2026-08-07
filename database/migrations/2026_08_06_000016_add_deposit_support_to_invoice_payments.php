<?php
// database/migrations/2026_08_06_000016_add_deposit_support_to_invoice_payments.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });

        // Raw MODIFY to make invoice_id nullable — a payment can now exist with no invoice
        // at all (pure margin/deposit held on a client's account).
        DB::statement('ALTER TABLE invoice_payments MODIFY invoice_id BIGINT UNSIGNED NULL');

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();

            // Nullable for now — backfilled from the linked invoice below, then tightened
            // to NOT NULL once every existing row has a value.
            $table->foreignId('bullion_client_id')->nullable()->after('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('direction')->nullable()->after('amount'); // in = receipt from client, out = payment to client
            $table->string('purpose')->nullable()->after('direction'); // deposit, margin, settlement, advance, ...
        });

        DB::statement('
            UPDATE invoice_payments ip
            INNER JOIN invoices i ON i.id = ip.invoice_id
            SET ip.bullion_client_id = i.bullion_client_id,
                ip.direction = CASE WHEN i.invoice_type = "purchase" THEN "out" ELSE "in" END
            WHERE ip.bullion_client_id IS NULL
        ');

        DB::statement('ALTER TABLE invoice_payments MODIFY bullion_client_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE invoice_payments MODIFY direction VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropForeign(['bullion_client_id']);
            $table->dropColumn(['bullion_client_id', 'direction', 'purpose']);
            $table->dropForeign(['invoice_id']);
        });

        DB::statement('ALTER TABLE invoice_payments MODIFY invoice_id BIGINT UNSIGNED NOT NULL');

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });
    }
};
