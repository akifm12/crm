<?php
// database/migrations/2026_08_08_000001_create_public_content_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_compliance_deadlines', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('sector')->nullable(); // SectorConfig slug, null = applies to all
            $table->enum('category', ['goaml', 'licensing', 'screening', 'training', 'reporting', 'other'])->default('other');
            $table->string('authority')->nullable(); // e.g. "UAE MOE", "FIU"
            $table->enum('recurrence', ['one_off', 'monthly', 'quarterly', 'annual', 'ongoing'])->default('annual');
            $table->date('next_due_date')->nullable();
            $table->string('source_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('resource_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('sector')->nullable();
            $table->enum('category', ['checklist', 'guide', 'template', 'reference'])->default('guide');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('news_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary');
            $table->longText('body')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_url', 512)->nullable()->unique();
            $table->enum('category', ['aml', 'sanctions', 'regulatory', 'industry', 'insight'])->default('regulatory');
            $table->enum('origin', ['rss', 'ai_digest'])->default('rss');
            $table->dateTime('published_at');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('news_items');
        Schema::dropIfExists('resource_documents');
        Schema::dropIfExists('public_compliance_deadlines');
    }
};
