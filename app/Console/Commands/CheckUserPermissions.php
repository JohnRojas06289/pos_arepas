<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CheckUserPermissions extends Command
{
    protected $signature = 'check:permissions {email?}';
    protected $description = 'Check user permissions';

    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@gmail.com';
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found: {$email}");
            return;
        }

        $this->info("User: {$user->name} ({$user->email})");
        $this->info("User ID: {$user->id}");
        
        $roles = $user->roles;
        $this->info("Roles: " . $roles->count());
        
        foreach ($roles as $role) {
            $this->info("  - {$role->name} (ID: {$role->id})");
            $permissions = $role->permissions;
            $this->info("    Permissions: " . $permissions->count());
        }
        
        $allPermissions = $user->getAllPermissions();
        $this->info("Total permissions: " . $allPermissions->count());
        foreach($allPermissions as $p) {
            $this->info(" - " . $p->name);
        }
    }
}
