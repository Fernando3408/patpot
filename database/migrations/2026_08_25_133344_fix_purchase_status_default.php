<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purchases')->where('status', 'ordered')->update(['status' => 'pending']);

        DB::statement("ALTER TABLE purchases ALTER COLUMN status SET DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::table('purchases')->where('status', 'pending')->update(['status' => 'ordered']);

        DB::statement("ALTER TABLE purchases ALTER COLUMN status SET DEFAULT 'ordered'");
    }
};
