<?php
// database/migrations/2026_08_08_000003_change_source_url_to_text_on_news_items.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropUnique(['source_url']);
        });

        DB::statement('ALTER TABLE news_items MODIFY source_url TEXT NULL');

        Schema::table('news_items', function (Blueprint $table) {
            $table->string('source_url_hash', 40)->nullable()->unique()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropUnique(['source_url_hash']);
            $table->dropColumn('source_url_hash');
        });

        DB::statement('ALTER TABLE news_items MODIFY source_url VARCHAR(512) NULL');

        Schema::table('news_items', function (Blueprint $table) {
            $table->unique('source_url');
        });
    }
};
