<?php
// database/migrations/2026_08_06_000015_add_invoice_type_to_tenant_invoice_sequences.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // invoice_type column already exists from the partially-applied first attempt of
        // this migration; guard so re-running is idempotent.
        if (! Schema::hasColumn('tenant_invoice_sequences', 'invoice_type')) {
            Schema::table('tenant_invoice_sequences', function (Blueprint $table) {
                $table->string('invoice_type')->nullable()->after('tenant_id');
            });
        }

        Schema::table('tenant_invoice_sequences', function (Blueprint $table) {
            // MySQL won't drop a unique index while a FK still depends on it — drop the FK
            // first, swap the index, then re-add the FK (it'll happily use the new
            // composite index since tenant_id is still its leading column).
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id']);
            $table->unique(['tenant_id', 'invoice_type']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_invoice_sequences', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'invoice_type']);
            $table->unique(['tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->dropColumn('invoice_type');
        });
    }
};
