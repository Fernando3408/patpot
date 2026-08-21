<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inputs', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit');

            // Inventario
            $table->decimal('stock', 12, 2)->default(0);
            $table->decimal('safety_stock', 12, 2)->default(0);

            // Consumo y planificación
            $table->decimal('weekly_consumption', 12, 2)->default(0);
            $table->integer('lead_time_days')->default(0);
            $table->decimal('target_weeks', 8, 2)->default(0);

            // Compras
            $table->decimal('min_purchase', 12, 2)->default(0);
            $table->decimal('purchase_multiple', 12, 2)->default(1);
            $table->decimal('unit_cost', 12, 2)->default(0);

            // Stock en tránsito
            $table->decimal('transit', 12, 2)->default(0);

            // Proveedor
            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inputs');
    }
};
