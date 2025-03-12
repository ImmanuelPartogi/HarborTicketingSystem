<?php

namespace Database\Seeders;

use App\Models\Ferry;
use Illuminate\Database\Seeder;

class FerrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample ferry data
        Ferry::create([
            'name' => 'KMP Serdang',
            'capacity_passenger' => 500,
            'capacity_vehicle_motorcycle' => 100,
            'capacity_vehicle_car' => 50,
            'capacity_vehicle_bus' => 10,
            'capacity_vehicle_truck' => 15,
            'status' => 'ACTIVE',
            'description' => 'Ferry kelas ekonomi dengan kapasitas 500 penumpang',
        ]);

        Ferry::create([
            'name' => 'KMP Toba Samosir',
            'capacity_passenger' => 300,
            'capacity_vehicle_motorcycle' => 50,
            'capacity_vehicle_car' => 30,
            'capacity_vehicle_bus' => 5,
            'capacity_vehicle_truck' => 8,
            'status' => 'ACTIVE',
            'description' => 'Ferry kecil untuk rute pendek',
        ]);

        Ferry::create([
            'name' => 'KMP Danau Toba',
            'capacity_passenger' => 700,
            'capacity_vehicle_motorcycle' => 150,
            'capacity_vehicle_car' => 80,
            'capacity_vehicle_bus' => 15,
            'capacity_vehicle_truck' => 20,
            'status' => 'ACTIVE',
            'description' => 'Ferry kelas utama dengan kapasitas besar',
        ]);

        Ferry::create([
            'name' => 'KMP Samosir Express',
            'capacity_passenger' => 400,
            'capacity_vehicle_motorcycle' => 80,
            'capacity_vehicle_car' => 40,
            'capacity_vehicle_bus' => 8,
            'capacity_vehicle_truck' => 12,
            'status' => 'MAINTENANCE',
            'description' => 'Ferry cepat untuk rute pendek (sedang dalam perawatan)',
        ]);
    }
}
