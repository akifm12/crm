<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tfs_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('title');                        // e.g. "Amending Four Entries ISIL Daesh..."
            $table->string('alert_url')->nullable();        // UN press release URL
            $table->json('alerted_names')->nullable();      // extracted names from UN page
            $table->json('screening_results')->nullable();  // per-name screening outcome
            $table->json('tfs_urls')->nullable();           // [{label, url, status, submitted_at, snapshot_path}]
            $table->string('recommended_response')->nullable(); // no_match | confirmed_match | partial_match
            $table->string('selected_response')->nullable();    // what was actually submitted
            $table->enum('status', ['draft','screened','submitted','partial'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tfs_submissions');
    }
};
