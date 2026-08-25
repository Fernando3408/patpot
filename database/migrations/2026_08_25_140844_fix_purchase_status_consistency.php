<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE purchases p
            JOIN (
                SELECT purchase_id,
                       SUM(ordered_quantity) AS total_ordered,
                       SUM(received_quantity) AS total_received
                FROM purchase_lines
                GROUP BY purchase_id
            ) pl ON pl.purchase_id = p.id
            SET p.status = CASE
                WHEN pl.total_received = 0 THEN 'pending'
                WHEN pl.total_received >= pl.total_ordered THEN 'received'
                ELSE 'partial'
            END
        ");
    }

    public function down(): void
    {
    }
};
