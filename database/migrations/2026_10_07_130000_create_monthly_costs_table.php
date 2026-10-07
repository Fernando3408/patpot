<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_costs', function (Blueprint $table): void {
            $table->id();
            $table->date('cost_on');
            $table->string('concept');
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['fixed', 'variable'])->default('variable');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['cost_on', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_costs');
    }
};
