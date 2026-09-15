<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = match (DB::getDriverName()) {
            'mysql' => "
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
            ",
            default => "
                UPDATE purchases
                SET status = CASE
                    WHEN pl.total_received = 0 THEN 'pending'
                    WHEN pl.total_received >= pl.total_ordered THEN 'received'
                    ELSE 'partial'
                END
                FROM (
                    SELECT purchase_id,
                           SUM(ordered_quantity) AS total_ordered,
                           SUM(received_quantity) AS total_received
                    FROM purchase_lines
                    GROUP BY purchase_id
                ) pl
                WHERE pl.purchase_id = purchases.id
            ",
        };

        DB::statement($sql);
    }

    public function down(): void {}
};
