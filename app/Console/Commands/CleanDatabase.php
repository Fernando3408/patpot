<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanDatabase extends Command
{
    protected $signature = 'db:clean';

    protected $description = 'Remove all business data keeping only admin user and roles';

    public function handle(): int
    {
        $tables = [
            'attachments',
            'reception_lines',
            'receptions',
            'retail',
            'order_lines',
            'orders',
            'production_lines',
            'productions',
            'purchase_lines',
            'purchases',
            'recipes',
            'prices',
            'stores',
            'products',
            'inputs',
            'suppliers',
            'customers',
            'inventory_movements',
            'audit_logs',
            'tasks',
            'login_logs',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::table('users')->where('email', '!=', 'admin@patpot.cl')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->info('Base de datos limpia. Solo queda admin@patpot.cl');

        return 0;
    }
}
