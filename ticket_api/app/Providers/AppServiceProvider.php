<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\SeoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the SeoService as a singleton
        $this->app->singleton(SeoService::class, function ($app) {
            return new SeoService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL versions < 5.7.7
        Schema::defaultStringLength(191);

        // Set default SEO metadata if the settings table exists
        if (Schema::hasTable('settings')) {
            $this->app->make(SeoService::class)->setDefaultSeo();
        }
    }
}
