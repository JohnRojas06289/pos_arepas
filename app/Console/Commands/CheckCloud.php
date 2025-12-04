<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckCloud extends Command
{
    protected $signature = 'check:cloud';
    protected $description = 'Check cloud connection';

    public function handle()
    {
        try {
            $user = DB::connection('cloud')->table('users')->first();
            if ($user) {
                $this->info("ID Type: " . gettype($user->id) . " Value: " . $user->id);
            } else {
                $this->info("Table empty");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
