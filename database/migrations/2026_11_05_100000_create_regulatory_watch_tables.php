<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulatory_updates', function (Blueprint $table) {
            $table->id();
            $table->string('source_key', 60);
            $table->string('fingerprint', 64)->unique();      // the same change is never recorded twice
            $table->string('title', 300);
            $table->string('url', 1000)->nullable();
            $table->string('change_type', 30);                // new, changed, removed, list_changed, reminder
            $table->text('summary')->nullable();
            $table->json('details')->nullable();
            $table->string('affected_sections', 500)->nullable();
            $table->string('status', 20)->default('new');     // new, reviewed, applied, dismissed
            $table->text('reviewer_note')->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'detected_at']);
        });

        Schema::create('regulatory_watch_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ran_at');
            $table->boolean('ok')->default(true);
            $table->json('sources')->nullable();              // [{key, title, ok, note}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulatory_watch_runs');
        Schema::dropIfExists('regulatory_updates');
    }
};
