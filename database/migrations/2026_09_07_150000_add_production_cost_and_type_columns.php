<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('production_cost', 12, 2)->nullable()->after('sale_price_box');
        });

        Schema::table('inputs', function (Blueprint $table) {
            $table->string('type', 20)->default('material')->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('production_cost');
        });

        Schema::table('inputs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
