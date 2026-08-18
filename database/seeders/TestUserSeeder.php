<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe existing test accounts to avoid stale password hashes
        DB::table('users')->whereIn('email', [
            'admin@darland.com',
            'user@darland.com',
        ])->delete();

        // Admin account
        DB::table('users')->insert([
            'name'       => 'Admin User',
            'username'   => 'darlandadmin',
            'email'      => 'admin@darland.com',
            'password'   => Hash::make('admin123'),
            'role'       => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Regular user account
        DB::table('users')->insert([
            'name'       => 'Test User',
            'username'   => 'darlanduser',
            'email'      => 'user@darland.com',
            'password'   => Hash::make('user123'),
            'role'       => 'ajdp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Test accounts created!');
        $this->command->info('Admin → email: admin@darland.com | password: admin123');
        $this->command->info('User  → email: user@darland.com  | password: user123');
    }
}
