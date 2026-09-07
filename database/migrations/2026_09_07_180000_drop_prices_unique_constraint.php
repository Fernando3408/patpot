<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE prices DROP FOREIGN KEY prices_customer_id_foreign');
        DB::statement('ALTER TABLE prices DROP FOREIGN KEY prices_product_id_foreign');
        DB::statement('ALTER TABLE prices DROP INDEX prices_customer_id_product_id_unique');

        Schema::table('prices', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('prices', function (Blueprint $table) {
            $table->unique(['customer_id', 'product_id']);
        });
    }
};
