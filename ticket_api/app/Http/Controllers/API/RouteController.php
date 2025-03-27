<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Display a listing of routes.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Route::query();

        // Filter by status if provided
        if ($request->has('active_only') && $request->active_only) {
            $query->where('status', 'ACTIVE');
        }

        // Order by created_at by default
        $routes = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'routes' => $routes
            ]
        ]);
    }

    /**
     * Display the specified route.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $route = Route::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'route' => $route
            ]
        ]);
    }

    /**
     * Store a newly created route in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|max:100',
            'destination' => 'required|string|max:100',
            'distance' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'motorcycle_price' => 'required|numeric|min:0',
            'car_price' => 'required|numeric|min:0',
            'bus_price' => 'required|numeric|min:0',
            'truck_price' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $route = Route::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Route created successfully',
                'data' => [
                    'route' => $route
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Route creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified route in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'sometimes|required|string|max:100',
            'destination' => 'sometimes|required|string|max:100',
            'distance' => 'sometimes|required|numeric|min:0',
            'duration' => 'sometimes|required|integer|min:1',
            'base_price' => 'sometimes|required|numeric|min:0',
            'motorcycle_price' => 'sometimes|required|numeric|min:0',
            'car_price' => 'sometimes|required|numeric|min:0',
            'bus_price' => 'sometimes|required|numeric|min:0',
            'truck_price' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:ACTIVE,INACTIVE',
            'status_reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $route = Route::findOrFail($id);
            $route->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Route updated successfully',
                'data' => [
                    'route' => $route
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Route update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified route from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $route = Route::findOrFail($id);
            $route->delete();

            return response()->json([
                'success' => true,
                'message' => 'Route deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Route deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for routes by origin and destination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'nullable|string',
            'destination' => 'nullable|string',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Route::query();

        if ($request->has('origin') && $request->origin) {
            $query->where('origin', 'like', '%' . $request->origin . '%');
        }

        if ($request->has('destination') && $request->destination) {
            $query->where('destination', 'like', '%' . $request->destination . '%');
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $routes = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'routes' => $routes
            ]
        ]);
    }

    /**
     * Get all available origins.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function origins(Request $request)
    {
        $query = Route::query();

        if ($request->has('active_only') && $request->active_only) {
            $query->where('status', 'ACTIVE');
        }

        $origins = $query->select('origin')
            ->distinct()
            ->orderBy('origin')
            ->get()
            ->pluck('origin');

        return response()->json([
            'success' => true,
            'data' => [
                'origins' => $origins
            ]
        ]);
    }

    /**
     * Get all available destinations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destinations(Request $request)
    {
        $query = Route::query();

        if ($request->has('active_only') && $request->active_only) {
            $query->where('status', 'ACTIVE');
        }

        $destinations = $query->select('destination')
            ->distinct()
            ->orderBy('destination')
            ->get()
            ->pluck('destination');

        return response()->json([
            'success' => true,
            'data' => [
                'destinations' => $destinations
            ]
        ]);
    }

    /**
     * Get destinations for a specific origin.
     *
     * @param  string  $origin
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destinationsForOrigin($origin, Request $request)
    {
        $query = Route::where('origin', $origin);

        if ($request->has('active_only') && $request->active_only) {
            $query->where('status', 'ACTIVE');
        }

        $destinations = $query->select('destination')
            ->distinct()
            ->orderBy('destination')
            ->get()
            ->pluck('destination');

        return response()->json([
            'success' => true,
            'data' => [
                'destinations' => $destinations
            ]
        ]);
    }

    /**
     * Update route status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACTIVE,INACTIVE',
            'status_reason' => 'nullable|string|max:255',
            'status_expiry_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $route = Route::findOrFail($id);
            $route->status = $request->status;
            $route->status_reason = $request->status_reason;
            $route->status_expiry_date = $request->status_expiry_date;
            $route->status_updated_at = now();
            $route->save();

            return response()->json([
                'success' => true,
                'message' => 'Route status updated successfully',
                'data' => [
                    'route' => $route
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Route status update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
