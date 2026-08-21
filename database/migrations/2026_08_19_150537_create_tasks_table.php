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
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('owner')->nullable();
            $table->date('due_on')->index();
            $table->string('priority')->default('medium')->index();
            $table->string('module')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->date('completed_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
