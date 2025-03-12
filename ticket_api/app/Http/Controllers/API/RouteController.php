<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    /**
     * Display a listing of routes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $routes = Route::where('status', 'ACTIVE')->get();

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
     * Search for routes by origin and destination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = Route::where('status', 'ACTIVE');

        if ($request->has('origin')) {
            $query->where('origin', 'like', '%' . $request->origin . '%');
        }

        if ($request->has('destination')) {
            $query->where('destination', 'like', '%' . $request->destination . '%');
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function origins()
    {
        $origins = Route::where('status', 'ACTIVE')
            ->select('origin')
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destinations()
    {
        $destinations = Route::where('status', 'ACTIVE')
            ->select('destination')
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destinationsForOrigin($origin)
    {
        $destinations = Route::where('status', 'ACTIVE')
            ->where('origin', $origin)
            ->select('destination')
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
}
