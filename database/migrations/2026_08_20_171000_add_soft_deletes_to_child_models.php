<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('purchases', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('productions', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('prices', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('recipes', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('shipments', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('order_lines', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('shipment_lines', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('purchase_lines', fn (Blueprint $t) => $t->softDeletes());
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('purchases', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('productions', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('prices', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('recipes', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('shipments', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('order_lines', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('shipment_lines', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('purchase_lines', fn (Blueprint $t) => $t->dropSoftDeletes());
    }
};
