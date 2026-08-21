<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('input_id')
                ->constrained('inputs')
                ->cascadeOnDelete();

            $table->decimal('qty_per_box', 12, 4);

            $table->timestamps();

            $table->unique(['product_id', 'input_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
