<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('discount');
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('discount_pct');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('discount', 5, 2)->default(0);
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->decimal('discount_pct', 5, 2)->nullable();
        });
    }
};
