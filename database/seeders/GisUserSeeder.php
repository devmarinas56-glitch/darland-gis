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
            // GIS accounts
            ['email' => 'gisadmin@dar.gov.ph', 'name' => 'GIS Administrator', 'username' => 'gisadmin', 'role' => 'admin', 'password' => 'admin123'],
            ['email' => 'gis@dar.gov.ph',       'name' => 'GIS Staff',         'username' => 'gisstaff', 'role' => 'user',  'password' => 'gis123'],
            // FMS mirror accounts (role=fms triggers redirect to FMS app)
            ['email' => 'admin@dar.gov.ph',     'name' => 'FMS Administrator', 'username' => 'fmsadmin', 'role' => 'fms',   'password' => 'admin123'],
        ];

        foreach ($users as $u) {
            $existing = DB::table('users')->where('email', $u['email'])->first();
            if ($existing) {
                DB::table('users')->where('email', $u['email'])->update([
                    'name'       => $u['name'],
                    'role'       => $u['role'],
                    'password'   => Hash::make($u['password']),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('users')->insert([
                    'name'              => $u['name'],
                    'username'          => $u['username'],
                    'email'             => $u['email'],
                    'password'          => Hash::make($u['password']),
                    'role'              => $u['role'],
                    'email_verified_at' => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
            echo "Seeded: {$u['email']} ({$u['role']})\n";
        }
    }
}
