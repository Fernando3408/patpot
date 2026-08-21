<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('ordered')->index();
            $table->date('ordered_on');
            $table->date('expected_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('purchase_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('input_id')->constrained()->restrictOnDelete();
            $table->decimal('ordered_quantity', 12, 4);
            $table->decimal('received_quantity', 12, 4)->default(0);
            $table->decimal('unit_cost', 12, 2);
            $table->timestamps();
            $table->unique(['purchase_id', 'input_id']);
        });
        Schema::create('productions', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('planned_boxes', 12, 4);
            $table->decimal('actual_boxes', 12, 4)->default(0);
            $table->string('status')->default('planned')->index();
            $table->date('planned_on');
            $table->date('completed_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->date('ordered_on');
            $table->date('delivery_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('order_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('boxes', 12, 4);
            $table->decimal('price_box', 12, 2);
            $table->decimal('discount_pct', 5, 2)->nullable();
            $table->decimal('dispatched_boxes', 12, 4)->default(0);
            $table->timestamps();
        });
        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->date('shipped_on');
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('shipment_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained()->restrictOnDelete();
            $table->decimal('boxes', 12, 4);
            $table->decimal('price_box', 12, 2);
            $table->timestamps();
        });
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('input_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind');
            $table->decimal('quantity', 12, 4);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('shipment_lines');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_lines');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('productions');
        Schema::dropIfExists('purchase_lines');
        Schema::dropIfExists('purchases');
    }
};
