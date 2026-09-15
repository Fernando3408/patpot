<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            $table->decimal('stock', 12, 3)->default(0)->change();
            $table->decimal('safety_stock', 12, 3)->default(0)->change();
            $table->decimal('transit', 12, 3)->default(0)->change();
            $table->decimal('weekly_consumption', 12, 3)->default(0)->change();
            $table->decimal('min_purchase', 12, 3)->default(0)->change();
            $table->decimal('purchase_multiple', 12, 3)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            $table->decimal('stock', 12, 2)->default(0)->change();
            $table->decimal('safety_stock', 12, 2)->default(0)->change();
            $table->decimal('transit', 12, 2)->default(0)->change();
            $table->decimal('weekly_consumption', 12, 2)->default(0)->change();
            $table->decimal('min_purchase', 12, 2)->default(0)->change();
            $table->decimal('purchase_multiple', 12, 2)->default(1)->change();
        });
    }
};
