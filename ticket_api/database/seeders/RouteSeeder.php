<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample route data
        Route::create([
            'origin' => 'Ajibata',
            'destination' => 'Tomok',
            'distance' => 10.5,
            'duration' => 30, // minutes
            'base_price' => 25000, // Rp
            'motorcycle_price' => 40000,
            'car_price' => 75000,
            'bus_price' => 150000,
            'truck_price' => 200000,
            'status' => 'ACTIVE',
        ]);

        Route::create([
            'origin' => 'Tomok',
            'destination' => 'Ajibata',
            'distance' => 10.5,
            'duration' => 30, // minutes
            'base_price' => 25000, // Rp
            'motorcycle_price' => 40000,
            'car_price' => 75000,
            'bus_price' => 150000,
            'truck_price' => 200000,
            'status' => 'ACTIVE',
        ]);

        Route::create([
            'origin' => 'Ajibata',
            'destination' => 'Ambarita',
            'distance' => 15.2,
            'duration' => 45, // minutes
            'base_price' => 35000, // Rp
            'motorcycle_price' => 50000,
            'car_price' => 85000,
            'bus_price' => 170000,
            'truck_price' => 220000,
            'status' => 'ACTIVE',
        ]);

        Route::create([
            'origin' => 'Ambarita',
            'destination' => 'Ajibata',
            'distance' => 15.2,
            'duration' => 45, // minutes
            'base_price' => 35000, // Rp
            'motorcycle_price' => 50000,
            'car_price' => 85000,
            'bus_price' => 170000,
            'truck_price' => 220000,
            'status' => 'ACTIVE',
        ]);

        Route::create([
            'origin' => 'Parapat',
            'destination' => 'Tomok',
            'distance' => 12.0,
            'duration' => 35, // minutes
            'base_price' => 30000, // Rp
            'motorcycle_price' => 45000,
            'car_price' => 80000,
            'bus_price' => 160000,
            'truck_price' => 210000,
            'status' => 'ACTIVE',
        ]);

        Route::create([
            'origin' => 'Tomok',
            'destination' => 'Parapat',
            'distance' => 12.0,
            'duration' => 35, // minutes
            'base_price' => 30000, // Rp
            'motorcycle_price' => 45000,
            'car_price' => 80000,
            'bus_price' => 160000,
            'truck_price' => 210000,
            'status' => 'ACTIVE',
        ]);
    }
}
