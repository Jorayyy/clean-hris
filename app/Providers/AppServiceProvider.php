<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cache settings for 24 hours to reduce DB load on every page load
        View::composer('*', function ($view) {
            $settings = Cache::remember('system_settings', 86400, function () {
                return AppSetting::first() ?: (object)['app_name' => 'HRIS Payroll', 'app_logo' => null];
            });
            $view->with('systemSettings', $settings);
        });
    }
}
