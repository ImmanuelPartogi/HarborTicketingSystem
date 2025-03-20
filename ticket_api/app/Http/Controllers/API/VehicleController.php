<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Route;
use App\Models\Ferry;
use App\Services\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    protected $vehicleService;

    /**
     * Create a new controller instance.
     *
     * @param VehicleService $vehicleService
     */
    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Get vehicle types and prices for a specific route.
     *
     * @param Request $request
     * @param int $routeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehiclePrices(Request $request, $routeId)
    {
        try {
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
                    'route' => [
                        'id' => $route->id,
                        'origin' => $route->origin,
                        'destination' => $route->destination
                    ]
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
                'error' => 'The requested route could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving vehicle prices: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve vehicle prices',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
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
            'weight' => 'required|numeric|min:0|max:50000',
            'length' => 'required|numeric|min:0|max:30',
            'width' => 'required|numeric|min:0|max:5',
            'height' => 'required|numeric|min:0|max:5',
            'ferry_id' => 'required|exists:ferries,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use service to validate dimensions
            $validationResult = $this->vehicleService->validateVehicleDimensions(
                $request->type,
                $request->weight,
                $request->length,
                $request->width,
                $request->height,
                $request->ferry_id
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid' => $validationResult['isValid'],
                    'message' => $validationResult['message'],
                    'dimension_limits' => $validationResult['dimensionLimits']
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating vehicle dimensions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate vehicle dimensions',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get user's saved vehicles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserVehicles(Request $request)
    {
        try {
            $vehicles = $this->vehicleService->getUserSavedVehicles($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'vehicles' => $vehicles,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving user vehicles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saved vehicles',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
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
            'weight' => 'nullable|numeric|min:0|max:50000',
            'length' => 'nullable|numeric|min:0|max:30',
            'width' => 'nullable|numeric|min:0|max:5',
            'height' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $checkResult = $this->vehicleService->checkVehicleForFerry(
                $request->ferry_id,
                $request->vehicle_type,
                $request->weight,
                $request->length,
                $request->width,
                $request->height
            );

            return response()->json([
                'success' => true,
                'data' => $checkResult
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ferry not found',
                'error' => 'The requested ferry could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error checking vehicle for ferry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check vehicle for ferry',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Add a new vehicle for the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUserVehicle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'license_plate' => 'required|string|max:20|regex:/^[A-Z0-9 -]+$/i',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30',
            'weight' => 'nullable|numeric|min:0|max:50000',
            'length' => 'nullable|numeric|min:0|max:30',
            'width' => 'nullable|numeric|min:0|max:5',
            'height' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vehicle = $this->vehicleService->addUserVehicle($request->user()->id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Vehicle added successfully',
                'data' => [
                    'vehicle' => $vehicle
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error adding user vehicle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add vehicle',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Update user's vehicle.
     *
     * @param Request $request
     * @param int $vehicleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserVehicle(Request $request, $vehicleId)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'license_plate' => 'sometimes|required|string|max:20|regex:/^[A-Z0-9 -]+$/i',
            'brand' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30',
            'weight' => 'nullable|numeric|min:0|max:50000',
            'length' => 'nullable|numeric|min:0|max:30',
            'width' => 'nullable|numeric|min:0|max:5',
            'height' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vehicle = $this->vehicleService->updateUserVehicle($request->user()->id, $vehicleId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully',
                'data' => [
                    'vehicle' => $vehicle
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
                'error' => 'The requested vehicle could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating user vehicle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vehicle',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Delete user's vehicle.
     *
     * @param Request $request
     * @param int $vehicleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUserVehicle(Request $request, $vehicleId)
    {
        try {
            $result = $this->vehicleService->deleteUserVehicle($request->user()->id, $vehicleId);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vehicle deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete vehicle',
                    'error' => 'The vehicle may be associated with active bookings'
                ], 400);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
                'error' => 'The requested vehicle could not be found or does not belong to this user'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting user vehicle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vehicle',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get vehicle types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehicleTypes()
    {
        try {
            $vehicleTypes = $this->vehicleService->getVehicleTypes();

            return response()->json([
                'success' => true,
                'data' => [
                    'vehicle_types' => $vehicleTypes
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving vehicle types: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve vehicle types',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
