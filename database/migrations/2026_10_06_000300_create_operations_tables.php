<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telkomsel_solutions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique();
            $table->string('name');
            $table->string('layer', 20); // learn | market | operate | enable | scale
            $table->string('role'); // e.g. "Acquisition Engine"
            $table->string('tagline');
            $table->text('description');
            $table->json('helps');
            $table->string('objective');
            $table->json('metrics');
            $table->json('addresses'); // bottleneck dimensions it is matched to
            $table->string('min_tier', 10)->default('bronze'); // tier needed to activate
            $table->string('pricing_label', 40); // included | activation_required | usage_cost | eligibility_required
            $table->string('price_note')->nullable();
            $table->json('tier_access'); // {bronze, silver, gold}
            $table->string('icon', 40);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('solution_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('telkomsel_solution_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('not_activated'); // active | activation_required | eligibility_check | not_activated | simulated
            $table->unsignedTinyInteger('step')->default(0);
            $table->json('meta')->nullable();
            $table->string('connection', 20)->default('simulated'); // connected | simulated | pending | unavailable
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'telkomsel_solution_id']);
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('solution_key', 30)->default('myads');
            $table->string('name');
            $table->string('objective', 30); // acquisition | retention | conversion
            $table->string('audience');
            $table->unsignedSmallInteger('radius_km')->default(2);
            $table->unsignedBigInteger('budget');
            $table->string('status', 20)->default('draft'); // draft | running | completed
            $table->unsignedInteger('reach')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->unsignedBigInteger('spend')->default(0);
            $table->unsignedBigInteger('attributed_revenue')->default(0);
            $table->string('source', 20)->default('simulated');
            $table->timestamp('launched_at')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku', 40);
            $table->string('category')->nullable();
            $table->unsignedBigInteger('purchase_price');
            $table->unsignedBigInteger('selling_price');
            $table->integer('stock')->default(0);
            $table->unsignedInteger('min_stock')->default(0);
            $table->string('supplier')->nullable();
            $table->string('unit', 20)->default('pcs');
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'sku']);
        });

        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10); // income | expense
            $table->date('entry_date');
            $table->string('category', 30);
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('cogs')->default(0); // COGS embedded in an inventory-linked sale
            $table->string('customer')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->string('payment_method', 20)->nullable();
            $table->text('notes')->nullable();
            $table->string('source', 20)->default('manual'); // manual | receipt_ocr | inventory | integration_demo
            $table->timestamps();
            $table->index(['business_id', 'entry_date']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12); // initial | purchase | sale | adjustment
            $table->integer('quantity'); // signed: + in, - out
            $table->unsignedBigInteger('unit_cost')->default(0);
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->date('moved_on');
            $table->foreignId('financial_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 20)->default('manual'); // manual | demo | integration_demo
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'moved_on']);
        });

        Schema::create('expense_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->json('parsed');
            $table->string('status', 20)->default('pending'); // pending | confirmed
            $table->foreignId('financial_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['expense_receipts', 'inventory_movements', 'financial_entries', 'products', 'campaigns', 'solution_activations', 'telkomsel_solutions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
