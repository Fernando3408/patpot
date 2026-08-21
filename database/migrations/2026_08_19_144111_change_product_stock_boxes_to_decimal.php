<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('stock_boxes', 12, 4)->default(0)->change();
            $table->decimal('min_stock_boxes', 12, 4)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->integer('stock_boxes')->default(0)->change();
            $table->integer('min_stock_boxes')->default(0)->change();
        });
    }
};
