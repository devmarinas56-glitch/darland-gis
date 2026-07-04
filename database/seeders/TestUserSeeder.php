<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::updateOrCreate(
            ['email' => 'admin@darland.com'],
            [
                'name'     => 'Admin User',
                'username' => 'admin',
                'email'    => 'admin@darland.com',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // Regular user account
        User::updateOrCreate(
            ['email' => 'user@darland.com'],
            [
                'name'     => 'Test User',
                'username' => 'testuser',
                'email'    => 'user@darland.com',
                'password' => Hash::make('user123'),
                'role'     => 'user',
            ]
        );

        $this->command->info('Test accounts created!');
        $this->command->info('Admin  → email: admin@darland.com  | password: admin123');
        $this->command->info('User   → email: user@darland.com   | password: user123');
    }
}
