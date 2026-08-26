<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->date('received_on');
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reception_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_line_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_cost', 12, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reception_lines');
        Schema::dropIfExists('receptions');
    }
};
