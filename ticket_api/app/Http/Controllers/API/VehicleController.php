<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Get vehicle types and prices for a specific route.
     *
     * @param Request $request
     * @param int $routeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehiclePrices(Request $request, $routeId)
    {
        $route = Route::findOrFail($routeId);

        $vehiclePrices = [
            [
                'type' => 'MOTORCYCLE',
                'type_name' => 'Motor',
                'price' => $route->motorcycle_price,
                'formatted_price' => 'Rp ' . number_format($route->motorcycle_price, 0, ',', '.'),
            ],
            [
                'type' => 'CAR',
                'type_name' => 'Mobil',
                'price' => $route->car_price,
                'formatted_price' => 'Rp ' . number_format($route->car_price, 0, ',', '.'),
            ],
            [
                'type' => 'BUS',
                'type_name' => 'Bus',
                'price' => $route->bus_price,
                'formatted_price' => 'Rp ' . number_format($route->bus_price, 0, ',', '.'),
            ],
            [
                'type' => 'TRUCK',
                'type_name' => 'Truk',
                'price' => $route->truck_price,
                'formatted_price' => 'Rp ' . number_format($route->truck_price, 0, ',', '.'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'vehicle_prices' => $vehiclePrices,
            ],
        ]);
    }

    /**
     * Validate vehicle dimensions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateDimensions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'weight' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'ferry_id' => 'required|exists:ferries,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Here we would implement vehicle dimension validation logic
        // based on the ferry's capacity and restrictions
        // For this example, we'll use some simple validation rules

        $isValid = true;
        $message = 'Vehicle dimensions are valid';

        // Example validation logic (would be more complex in a real system)
        switch ($request->type) {
            case 'MOTORCYCLE':
                if ($request->weight > 500 || $request->length > 2.5 || $request->width > 1 || $request->height > 1.5) {
                    $isValid = false;
                    $message = 'The motorcycle exceeds the allowed dimensions or weight';
                }
                break;
            case 'CAR':
                if ($request->weight > 3500 || $request->length > 5 || $request->width > 2.2 || $request->height > 2) {
                    $isValid = false;
                    $message = 'The car exceeds the allowed dimensions or weight';
                }
                break;
            case 'BUS':
                if ($request->weight > 10000 || $request->length > 12 || $request->width > 2.5 || $request->height > 3.5) {
                    $isValid = false;
                    $message = 'The bus exceeds the allowed dimensions or weight';
                }
                break;
            case 'TRUCK':
                if ($request->weight > 20000 || $request->length > 15 || $request->width > 2.5 || $request->height > 4) {
                    $isValid = false;
                    $message = 'The truck exceeds the allowed dimensions or weight';
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => $isValid,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Get user's saved vehicles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserVehicles(Request $request)
    {
        // Get unique vehicles from user's bookings
        $vehicles = $request->user()->bookings()
            ->with('vehicles')
            ->get()
            ->pluck('vehicles')
            ->flatten()
            ->unique('license_plate')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'vehicles' => $vehicles,
            ],
        ]);
    }

    /**
     * Check if a vehicle is valid for a ferry.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkVehicleForFerry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ferry_id' => 'required|exists:ferries,id',
            'vehicle_type' => 'required|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'weight' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the ferry has capacity for this vehicle type
        $ferry = \App\Models\Ferry::findOrFail($request->ferry_id);

        $capacityField = '';
        switch ($request->vehicle_type) {
            case 'MOTORCYCLE':
                $capacityField = 'capacity_vehicle_motorcycle';
                break;
            case 'CAR':
                $capacityField = 'capacity_vehicle_car';
                break;
            case 'BUS':
                $capacityField = 'capacity_vehicle_bus';
                break;
            case 'TRUCK':
                $capacityField = 'capacity_vehicle_truck';
                break;
        }

        $hasCapacity = $ferry->$capacityField > 0;

        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => $hasCapacity,
                'message' => $hasCapacity
                    ? 'Vehicle is valid for this ferry'
                    : 'This ferry does not accept ' . strtolower($request->vehicle_type) . 's',
                'capacity' => $ferry->$capacityField,
            ],
        ]);
    }
}
