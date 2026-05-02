<?php
// database/migrations/2024_06_04_000001_create_goaml_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One-time static config per tenant (MLRO + entity details)
        Schema::create('goaml_static_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            // Reporting entity
            $table->string('rentity_id');           // goAML system ID
            $table->string('entity_name');
            $table->string('entity_address');
            $table->string('entity_city');
            $table->string('entity_country_code', 3)->default('UAE');
            $table->string('entity_state')->nullable();
            // MLRO (reporting person)
            $table->enum('mlro_gender', ['M', 'F'])->default('M');
            $table->string('mlro_first_name');
            $table->string('mlro_last_name');
            $table->string('mlro_ssn');             // passport / Emirates ID
            $table->string('mlro_id_number');
            $table->string('mlro_nationality', 3);
            $table->string('mlro_email');
            $table->string('mlro_occupation')->default('MLRO');
            $table->timestamps();
        });

        // Report history
        Schema::create('goaml_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bullion_client_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('report_type', ['DPMSR', 'STR', 'SAR'])->default('DPMSR');
            $table->string('entity_reference');       // invoice number
            $table->string('client_name');
            $table->decimal('estimated_value', 15, 2);
            $table->decimal('disposed_value', 15, 2);
            $table->string('currency_code', 3)->default('AED');
            $table->decimal('size', 10, 3)->nullable();
            $table->string('size_uom')->default('Grams');
            $table->date('registration_date');
            $table->text('reason')->nullable();
            $table->text('comments')->nullable();
            $table->string('xml_file_path')->nullable();
            $table->string('xml_file_name')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goaml_reports');
        Schema::dropIfExists('goaml_static_configs');
    }
};
