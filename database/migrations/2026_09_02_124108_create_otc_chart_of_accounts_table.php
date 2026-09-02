<?php
// B-Book chart of accounts — deliberately a fully separate table from the
// existing chart_of_accounts (A-Book). ReportingService::trialBalance() sums
// every active account for a tenant unconditionally, so adding OTC accounts
// to the shared table would silently leak into A-Book's existing Balance
// Sheet / Income Statement. This isolation is intentional, not an oversight.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otc_chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('otc_chart_of_accounts')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type'); // asset, liability, equity, income, expense
            $table->string('subtype')->nullable();
            $table->string('normal_balance'); // debit, credit
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otc_chart_of_accounts');
    }
};
