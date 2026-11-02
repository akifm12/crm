<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory items (gold bars, coins, jewelry, silver, etc.)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->string('name_arabic', 200)->nullable();
            $table->string('category', 50)->nullable(); // gold_bar, gold_coin, jewelry, silver_bar, other
            $table->string('unit', 20)->default('pcs'); // pcs, grams, oz, kg
            $table->decimal('gold_purity', 5, 1)->nullable(); // 999.9, 916.0, 750.0, etc.
            $table->decimal('weight_per_unit_grams', 12, 4)->nullable(); // weight of one unit in grams
            $table->decimal('current_price', 18, 4)->default(0); // selling price per unit (AED)
            $table->decimal('cost_price', 18, 4)->default(0);    // cost per unit (AED)
            $table->decimal('stock_qty', 18, 4)->default(0);     // running total
            $table->decimal('vat_rate', 5, 2)->default(0);       // 0 for investment gold, 5 for jewelry
            $table->unsignedBigInteger('sales_account_id')->nullable();
            $table->unsignedBigInteger('inventory_account_id')->nullable();
            $table->unsignedBigInteger('cogs_account_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });

        // Inventory movements (purchase in, sale out, adjustment)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->string('movement_type', 30); // purchase_in, sale_out, adjustment_in, adjustment_out, opening
            $table->date('movement_date');
            $table->decimal('quantity', 18, 4); // positive = in, negative = out
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'inventory_item_id']);
        });

        // Customer invoices (AR)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('client_id')->nullable(); // → bullion_clients
            $table->string('invoice_number', 100)->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('draft'); // draft, sent, approved, paid, partial, void
            $table->string('customer_name', 200)->nullable();
            $table->string('customer_trn', 50)->nullable();
            $table->string('customer_address', 500)->nullable();
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('vat_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->decimal('amount_due', 18, 2)->default(0);
            $table->string('currency_code', 10)->default('AED');
            $table->decimal('exchange_rate', 10, 6)->default(1.0);
            $table->string('po_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'invoice_date']);
        });

        // Invoice line items
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('inventory_item_id')->nullable();
            $table->unsignedBigInteger('chart_of_account_id')->nullable(); // revenue account
            $table->integer('line_order')->default(0);
            $table->string('description', 500)->nullable();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->string('unit', 20)->default('pcs');
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->decimal('line_amount', 18, 2)->default(0); // qty * unit_price
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('vat_amount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0); // line_amount + vat_amount
            $table->decimal('gold_weight_grams', 18, 4)->nullable(); // total gold weight for this line
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
    }
};
