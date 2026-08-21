<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table): void {
            $table->unsignedInteger('planned_boxes')->change();
            $table->unsignedInteger('actual_boxes')->default(0)->change();
        });

        Schema::table('order_lines', function (Blueprint $table): void {
            $table->unsignedInteger('boxes')->change();
            $table->unsignedInteger('dispatched_boxes')->default(0)->change();
        });

        Schema::table('shipment_lines', function (Blueprint $table): void {
            $table->unsignedInteger('boxes')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table): void {
            $table->decimal('planned_boxes', 12, 4)->change();
            $table->decimal('actual_boxes', 12, 4)->default(0)->change();
        });

        Schema::table('order_lines', function (Blueprint $table): void {
            $table->decimal('boxes', 12, 4)->change();
            $table->decimal('dispatched_boxes', 12, 4)->default(0)->change();
        });

        Schema::table('shipment_lines', function (Blueprint $table): void {
            $table->decimal('boxes', 12, 4)->change();
        });
    }
};
