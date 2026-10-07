<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_input_consumptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('input_id')->constrained()->restrictOnDelete();
            $table->decimal('boxes', 12, 4);
            $table->decimal('qty_per_box', 12, 4);
            $table->decimal('quantity', 12, 4);
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['order_id', 'reversed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_input_consumptions');
    }
};
