<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The base table-creation migration was later edited to include this column
        // directly, making this migration a no-op on any database created since —
        // only add it here for older databases that predate that edit.
        if (! Schema::hasColumn('client_shareholders', 'passport_expiry')) {
            Schema::table('client_shareholders', function (Blueprint $table) {
                $table->date('passport_expiry')->nullable()->after('passport_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('client_shareholders', 'passport_expiry')) {
            Schema::table('client_shareholders', function (Blueprint $table) {
                $table->dropColumn('passport_expiry');
            });
        }
    }
};
