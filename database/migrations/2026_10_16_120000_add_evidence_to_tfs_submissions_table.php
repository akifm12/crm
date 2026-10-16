<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tfs_submissions', function (Blueprint $table) {
            $table->text('survey_url')->nullable()->after('title');
            $table->string('survey_evidence_path')->nullable();   // screenshot of "Survey completed successfully"
            $table->string('search_evidence_path')->nullable();   // screenshot of the client-list search (not found)
        });
    }

    public function down(): void
    {
        Schema::table('tfs_submissions', function (Blueprint $table) {
            $table->dropColumn(['survey_url', 'survey_evidence_path', 'search_evidence_path']);
        });
    }
};
