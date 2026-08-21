<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('business_name');   // Razón social
            $table->string('trade_name')->nullable(); // Nombre de fantasía
            $table->string('rut')->nullable();

            $table->string('type')->nullable();    // ej: retail, distribuidor, mayorista
            $table->string('channel')->nullable();  // ej: Cencosud/Jumbo, SMU/Unimarc, otros

            $table->decimal('discount', 5, 2)->default(0); // % descuento por cliente

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
