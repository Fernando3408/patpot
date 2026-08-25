<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE orders o
            JOIN (
                SELECT order_id,
                       SUM(boxes) AS total_boxes,
                       SUM(dispatched_boxes) AS total_dispatched
                FROM order_lines
                GROUP BY order_id
            ) ol ON ol.order_id = o.id
            SET o.status = CASE
                WHEN ol.total_dispatched = 0 THEN 'pending'
                WHEN ol.total_dispatched >= ol.total_boxes THEN 'completed'
                ELSE 'partial'
            END
            WHERE o.status != CASE
                WHEN ol.total_dispatched = 0 THEN 'pending'
                WHEN ol.total_dispatched >= ol.total_boxes THEN 'completed'
                ELSE 'partial'
            END
        ");
    }

    public function down(): void
    {
    }
};
