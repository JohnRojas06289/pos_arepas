<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDefaultConnection extends Command
{
    protected $signature = 'check:default';
    protected $description = 'Check default connection';

    public function handle()
    {
        $default = config('database.default');
        $this->info("Default Connection: " . $default);
        
        try {
            $pdo = DB::connection()->getPdo();
            $this->info("Connected to: " . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
        } catch (\Exception $e) {
            $this->error("Connection Error: " . $e->getMessage());
        }
    }
}
