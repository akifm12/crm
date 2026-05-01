<?php
// database/migrations/create_crm_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Main client record ─────────────────────────────────────────────
        Schema::create('crm_clients', function (Blueprint $table) {
            $table->id();

            // Pipeline
            $table->enum('stage', [
                'lead', 'qualified', 'proposal_sent',
                'negotiation', 'onboarding', 'active', 'inactive'
            ])->default('lead');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');

            // Company profile (mapped from companies table)
            $table->string('company_name');
            $table->string('license_number')->nullable();
            $table->date('license_issue')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('license_authority')->nullable();
            $table->string('legal_status')->nullable();   // LLC, Free Zone, etc.
            $table->string('country_inc')->default('UAE');
            $table->string('regulator')->nullable();
            $table->string('ejari')->nullable();
            $table->string('trn')->nullable();
            $table->text('address')->nullable();

            // Contact
            $table->string('contact_person')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Services & relationship
            $table->json('services')->nullable();         // which BA services they use
            $table->string('relationship_manager')->nullable();
            $table->date('client_since')->nullable();
            $table->text('notes')->nullable();            // general notes

            // Tenant portal (auto-created)
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('portal_type', ['bullion', 'real_estate', 'other', 'none'])->default('none');

            // Meta
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Shareholders ───────────────────────────────────────────────────
        Schema::create('crm_shareholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained()->cascadeOnDelete();
            $table->string('shareholder_name');
            $table->date('birthdate')->nullable();
            $table->string('nationality')->nullable();
            $table->string('passport')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            $table->boolean('is_ubo')->default(false);
            $table->timestamps();
        });

        // ── Contact persons ────────────────────────────────────────────────
        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // ── Documents ──────────────────────────────────────────────────────
        Schema::create('crm_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_label');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Communication notes ────────────────────────────────────────────
        Schema::create('crm_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['note', 'call', 'email', 'meeting', 'whatsapp'])->default('note');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->datetime('interaction_at')->nullable(); // when the interaction happened
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Tasks / follow-ups ─────────────────────────────────────────────
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained()->cascadeOnDelete();
            $table->string('task_description');
            $table->date('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // ── SLA templates ──────────────────────────────────────────────────
        Schema::create('sla_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // e.g. "AML Consulting Retainer"
            $table->string('service_type');
            $table->text('description')->nullable();
            $table->text('scope_of_work');                 // what BA will do
            $table->text('client_obligations')->nullable();// what client must do
            $table->text('deliverables')->nullable();
            $table->string('duration')->nullable();        // e.g. "12 months"
            $table->decimal('default_fee', 10, 2)->nullable();
            $table->string('fee_frequency')->nullable();   // monthly, quarterly, annual
            $table->text('payment_terms')->nullable();
            $table->text('termination_clause')->nullable();
            $table->text('governing_law')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── SLAs (issued to clients) ───────────────────────────────────────
        Schema::create('crm_slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sla_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sla_reference')->unique(); // e.g. BA-SLA-2024-001
            $table->string('name');
            $table->text('scope_of_work');
            $table->text('client_obligations')->nullable();
            $table->text('deliverables')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->string('fee_frequency')->nullable();
            $table->text('payment_terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'signed', 'active', 'expired', 'terminated'])->default('draft');
            $table->string('signed_copy_path')->nullable(); // uploaded signed PDF
            $table->date('signed_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Quotation templates ────────────────────────────────────────────
        Schema::create('quotation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service_type');
            $table->text('description')->nullable();
            $table->json('line_items');   // [{description, qty, unit_price}]
            $table->text('terms')->nullable();
            $table->integer('validity_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Quotations (issued to clients) ─────────────────────────────────
        Schema::create('crm_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('quotation_reference')->unique(); // e.g. BA-QT-2024-001
            $table->string('subject');
            $table->json('line_items');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('terms')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_quotations');
        Schema::dropIfExists('quotation_templates');
        Schema::dropIfExists('crm_slas');
        Schema::dropIfExists('sla_templates');
        Schema::dropIfExists('crm_tasks');
        Schema::dropIfExists('crm_notes');
        Schema::dropIfExists('crm_documents');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_shareholders');
        Schema::dropIfExists('crm_clients');
    }
};
