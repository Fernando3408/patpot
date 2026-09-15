<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table): void {
            $table->decimal('freight_cost', 12, 2)->default(0)->after('total');
            $table->decimal('management_cost', 12, 2)->default(0)->after('freight_cost');
            $table->decimal('other_cost', 12, 2)->default(0)->after('management_cost');
        });

        Schema::table('shipment_lines', function (Blueprint $table): void {
            $table->decimal('cost_box', 12, 2)->default(0)->after('price_box');
            $table->decimal('variable_cost_box', 12, 2)->default(0)->after('cost_box');
        });
    }

    public function down(): void
    {
        Schema::table('shipment_lines', function (Blueprint $table): void {
            $table->dropColumn(['cost_box', 'variable_cost_box']);
        });

        Schema::table('shipments', function (Blueprint $table): void {
            $table->dropColumn(['freight_cost', 'management_cost', 'other_cost']);
        });
    }
};
