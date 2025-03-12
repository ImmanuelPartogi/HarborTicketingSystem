<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ferry;
use Illuminate\Http\Request;

class FerryController extends Controller
{
    /**
     * Display a listing of ferries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $ferries = Ferry::where('status', 'ACTIVE')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ferries' => $ferries
            ]
        ]);
    }

    /**
     * Display the specified ferry.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $ferry = Ferry::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'ferry' => $ferry
            ]
        ]);
    }

    /**
     * Get ferries by capacity requirements.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCapacity(Request $request)
    {
        $query = Ferry::where('status', 'ACTIVE');

        if ($request->has('passenger_count') && $request->passenger_count > 0) {
            $query->where('capacity_passenger', '>=', $request->passenger_count);
        }

        if ($request->has('motorcycle_count') && $request->motorcycle_count > 0) {
            $query->where('capacity_vehicle_motorcycle', '>=', $request->motorcycle_count);
        }

        if ($request->has('car_count') && $request->car_count > 0) {
            $query->where('capacity_vehicle_car', '>=', $request->car_count);
        }

        if ($request->has('bus_count') && $request->bus_count > 0) {
            $query->where('capacity_vehicle_bus', '>=', $request->bus_count);
        }

        if ($request->has('truck_count') && $request->truck_count > 0) {
            $query->where('capacity_vehicle_truck', '>=', $request->truck_count);
        }

        $ferries = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ferries' => $ferries
            ]
        ]);
    }
}
