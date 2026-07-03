<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandLot;

class LandLotSeeder extends Seeder
{
    public function run(): void
    {
        $lots = [
            [
                'land_id' => '10290',
                'owner_name' => 'Maria Santos',
                'barangay' => 'Anonas',
                'location' => 'Brgy. Anonas, Urdaneta City',
                'land_type' => 'residential',
                'area' => 520,
                'status' => 'registered',
                'date_registered' => '2023-03-10',
                'geojson' => null,
            ],
            [
                'land_id' => '10292',
                'owner_name' => 'Pedro Reyes',
                'barangay' => 'Anonas',
                'location' => 'Brgy. Anonas, Urdaneta City',
                'land_type' => 'residential',
                'area' => 480,
                'status' => 'registered',
                'date_registered' => '2023-04-15',
                'geojson' => null,
            ],
            [
                'land_id' => '10293',
                'owner_name' => 'Richard Alden',
                'barangay' => 'Anonas',
                'location' => 'Brgy. Anonas, Urdaneta City',
                'land_type' => 'residential',
                'area' => 600,
                'status' => 'registered',
                'date_registered' => '2023-05-12',
                'geojson' => null,
            ],
            [
                'land_id' => '10295',
                'owner_name' => 'Ana Villanueva',
                'barangay' => 'Palina East',
                'location' => 'Brgy. Palina East, Urdaneta City',
                'land_type' => 'agricultural',
                'area' => 750,
                'status' => 'registered',
                'date_registered' => '2023-02-20',
                'geojson' => null,
            ],
            [
                'land_id' => '10296',
                'owner_name' => 'Jose Cruz',
                'barangay' => 'Anonas',
                'location' => 'Brgy. Anonas, Urdaneta City',
                'land_type' => 'residential',
                'area' => 420,
                'status' => 'pending',
                'date_registered' => null,
                'geojson' => null,
            ],
            [
                'land_id' => '10298',
                'owner_name' => 'Luis Mendoza',
                'barangay' => 'Palina East',
                'location' => 'Brgy. Palina East, Urdaneta City',
                'land_type' => 'commercial',
                'area' => 900,
                'status' => 'registered',
                'date_registered' => '2023-01-08',
                'geojson' => null,
            ],
            [
                'land_id' => '10299',
                'owner_name' => 'Carla Bautista',
                'barangay' => 'Anonas',
                'location' => 'Brgy. Anonas, Urdaneta City',
                'land_type' => 'residential',
                'area' => 560,
                'status' => 'registered',
                'date_registered' => '2023-06-01',
                'geojson' => null,
            ],
        ];

        foreach ($lots as $lot) {
            LandLot::updateOrCreate(['land_id' => $lot['land_id']], $lot);
        }

        $this->command->info('Land lots seeded!');
    }
}
