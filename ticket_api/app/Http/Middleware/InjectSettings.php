<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class InjectSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get settings from cache or database
        $settings = $this->getSettings();

        // Share settings with all views
        View::share('siteSettings', $settings);

        return $next($request);
    }

    /**
     * Get all settings with caching
     *
     * @return array
     */
    private function getSettings()
    {
        return Cache::remember('all_settings', 60, function () {
            $settingsCollection = Setting::all();

            $settings = [];
            foreach ($settingsCollection as $setting) {
                $settings[$setting->key] = $setting->value;
            }

            return $settings;
        });
    }
}
