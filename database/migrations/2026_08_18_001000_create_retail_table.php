<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->boolean('cataloged')->default(true); // si el SKU está catalogado en esa sala

            $table->decimal('stock_units', 12, 2)->default(0);
            $table->decimal('transit_units', 12, 2)->default(0);
            $table->decimal('weekly_sales', 12, 2)->default(0);

            $table->decimal('min_stock', 12, 2)->default(0);
            $table->decimal('reorder_point', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['store_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retail');
    }
};
