<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use App\Models\Route;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
{
    // Get all landing page settings
    $settings = $this->getLandingPageSettings();

    // Get all active routes
    $allRoutes = $this->getAllRoutes();

    // Also set popular routes for compatibility
    $popularRoutes = $allRoutes;

    // Change 'landing' to 'welcome' to match your actual view file
    return view('welcome', compact('settings', 'allRoutes', 'popularRoutes'));
}

    private function getLandingPageSettings()
    {
        return Cache::remember('landing_page_settings', 3600, function () {
            $settingsCollection = Setting::all();

            $settings = [];
            foreach ($settingsCollection as $setting) {
                $settings[$setting->key] = $setting->value;
            }

            return $settings;
        });
    }

    private function getAllRoutes()
    {
        return Cache::remember('all_routes', 3600, function () {
            return Route::where('status', 'ACTIVE')
                ->orderBy('updated_at', 'desc')
                ->get();
        });
    }
}
