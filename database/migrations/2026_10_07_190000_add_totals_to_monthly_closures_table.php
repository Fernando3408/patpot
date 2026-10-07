<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_closures', function (Blueprint $table): void {
            $table->decimal('sales', 14, 2)->default(0)->after('month');
            $table->decimal('product_cost', 14, 2)->default(0)->after('sales');
            $table->decimal('expenses', 14, 2)->default(0)->after('product_cost');
            $table->decimal('monthly_costs', 14, 2)->default(0)->after('expenses');
            $table->decimal('net_profit', 14, 2)->default(0)->after('monthly_costs');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_closures', fn (Blueprint $table) => $table->dropColumn(['sales', 'product_cost', 'expenses', 'monthly_costs', 'net_profit']));
    }
};
