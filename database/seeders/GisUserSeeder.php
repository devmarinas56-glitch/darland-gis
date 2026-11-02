<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GisUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'              => 'GIS Administrator',
                'username'          => 'gisadmin',
                'email'             => 'gisadmin@dar.gov.ph',
                'password'          => Hash::make('admin123'),
                'role'              => 'admin',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'GIS Staff',
                'username'          => 'gisstaff',
                'email'             => 'gis@dar.gov.ph',
                'password'          => Hash::make('gis123'),
                'role'              => 'user',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];

        foreach ($users as $data) {
            // Upsert — update if exists, insert if not
            $existing = DB::table('users')->where('email', $data['email'])->first();
            if ($existing) {
                DB::table('users')->where('email', $data['email'])->update([
                    'name'     => $data['name'],
                    'role'     => $data['role'],
                    'password' => $data['password'],
                    'updated_at' => now(),
                ]);
                echo "Updated: {$data['email']}\n";
            } else {
                DB::table('users')->insert($data);
                echo "Created: {$data['email']}\n";
            }
        }
    }
}
