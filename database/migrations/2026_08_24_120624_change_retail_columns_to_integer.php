<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retail', function (Blueprint $table): void {
            $table->unsignedInteger('stock_units')->default(0)->change();
            $table->unsignedInteger('transit_units')->default(0)->change();
            $table->unsignedInteger('weekly_sales')->default(0)->change();
            $table->unsignedInteger('min_stock')->default(0)->change();
            $table->unsignedInteger('reorder_point')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('retail', function (Blueprint $table): void {
            $table->decimal('stock_units', 12, 2)->default(0)->change();
            $table->decimal('transit_units', 12, 2)->default(0)->change();
            $table->decimal('weekly_sales', 12, 2)->default(0)->change();
            $table->decimal('min_stock', 12, 2)->default(0)->change();
            $table->decimal('reorder_point', 12, 2)->default(0)->change();
        });
    }
};
