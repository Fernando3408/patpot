<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purchases')->where('status', 'ordered')->update(['status' => 'pending']);

        Schema::table('purchases', function (Blueprint $table): void {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        DB::table('purchases')->where('status', 'pending')->update(['status' => 'ordered']);

        Schema::table('purchases', function (Blueprint $table): void {
            $table->string('status')->default('ordered')->change();
        });
    }
};
