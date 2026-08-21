<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->string('code', 100)->unique()->after('id');
            $table->string('contact')->nullable()->after('channel');
            $table->string('email')->nullable()->after('contact');
            $table->string('payment_terms', 100)->nullable()->after('email');
            $table->unique('rut');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropUnique(['rut']);
            $table->dropColumn(['code', 'contact', 'email', 'payment_terms']);
        });
    }
};
