<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE retail MODIFY stock_units INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY transit_units INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY weekly_sales INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY min_stock INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY reorder_point INT UNSIGNED NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE retail MODIFY stock_units DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY transit_units DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY weekly_sales DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY min_stock DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE retail MODIFY reorder_point DECIMAL(12,2) NOT NULL DEFAULT 0');
    }
};
