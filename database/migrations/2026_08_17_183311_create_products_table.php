<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('sku')->unique();
            $table->integer('grams');
            $table->integer('units_per_box');
            $table->integer('stock_boxes')->default(0);
            $table->integer('min_stock_boxes')->default(0);
            $table->decimal('sale_price_box', 10, 2);
            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
