<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_closures', function (Blueprint $table): void {
            $table->id();
            $table->string('month', 7)->unique();
            $table->timestamp('closed_at');
            $table->foreignId('closed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closures');
    }
};
