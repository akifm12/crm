<?php
// database/migrations/create_bullion_client_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Main bullion client record ─────────────────────────────────────
        Schema::create('bullion_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Client type
            $table->enum('client_type', ['individual', 'corporate'])->default('corporate');

            // Company profile
            $table->string('company_name');
            $table->string('trade_license_no')->nullable();
            $table->date('trade_license_issue')->nullable();
            $table->date('trade_license_expiry')->nullable();
            $table->string('trn_number')->nullable();
            $table->string('ejari_number')->nullable();
            $table->string('legal_form')->nullable(); // LLC, Sole Est, Free Zone, etc.
            $table->string('country_of_incorporation')->default('UAE');
            $table->string('business_activity')->nullable();
            $table->text('nature_of_business')->nullable();
            $table->text('registered_address')->nullable();
            $table->text('operating_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Individual fields (when client_type = individual)
            $table->string('full_name')->nullable();
            $table->string('name_arabic')->nullable();
            $table->string('nationality')->nullable();
            $table->date('dob')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('eid_number')->nullable();
            $table->date('eid_expiry')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer_name')->nullable();
            $table->boolean('pep_status')->default(false);
            $table->text('pep_details')->nullable();

            // AML/CDD fields
            $table->json('source_of_funds')->nullable();
            $table->string('source_of_funds_other')->nullable();
            $table->json('source_of_wealth')->nullable();
            $table->string('source_of_wealth_other')->nullable();
            $table->text('purpose_of_relationship')->nullable();
            $table->decimal('expected_monthly_volume', 15, 2)->nullable();
            $table->string('expected_monthly_frequency')->nullable();
            $table->json('countries_involved')->nullable();
            $table->enum('cdd_type', ['standard', 'enhanced'])->default('standard');

            // Risk
            $table->enum('risk_rating', ['low', 'medium', 'high'])->nullable();
            $table->date('next_review_date')->nullable();
            $table->text('risk_notes')->nullable();

            // Status & screening
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending');
            $table->enum('screening_status', ['not_screened', 'clear', 'match', 'pending'])->default('not_screened');
            $table->timestamp('screening_date')->nullable();
            $table->string('screening_reference')->nullable();
            $table->json('screening_result')->nullable();

            // Declarations
            $table->boolean('decl_pep')->default(false);
            $table->boolean('decl_supply_chain')->default(false);
            $table->boolean('decl_cahra')->default(false);
            $table->boolean('decl_source_of_funds')->default(false);
            $table->boolean('decl_sanctions')->default(false);
            $table->boolean('decl_ubo')->default(false);
            $table->boolean('decl_master_signed')->default(false);
            $table->string('master_declaration_path')->nullable(); // uploaded signed PDF

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Authorised signatories ─────────────────────────────────────────
        Schema::create('client_signatories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bullion_client_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('nationality')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('eid_number')->nullable();
            $table->date('eid_expiry')->nullable();
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // ── Shareholders ───────────────────────────────────────────────────
        Schema::create('client_shareholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bullion_client_id')->constrained()->cascadeOnDelete();
            $table->enum('shareholder_type', ['individual', 'corporate']);
            $table->string('name');
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            // Individual fields
            $table->string('nationality')->nullable();
            $table->date('dob')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('eid_number')->nullable();
            // Corporate fields
            $table->string('country')->nullable();
            $table->string('registration_number')->nullable();
            $table->boolean('is_ubo')->default(false);
            $table->timestamps();
        });

        // ── Ultimate Beneficial Owners ─────────────────────────────────────
        Schema::create('client_ubos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bullion_client_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('nationality')->nullable();
            $table->date('dob')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('eid_number')->nullable();
            $table->date('eid_expiry')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            $table->boolean('pep_status')->default(false);
            $table->text('pep_details')->nullable();
            $table->text('source_of_wealth')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_ubos');
        Schema::dropIfExists('client_shareholders');
        Schema::dropIfExists('client_signatories');
        Schema::dropIfExists('bullion_clients');
    }
};
