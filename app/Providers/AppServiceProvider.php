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
        // Cache settings to reduce DB load on every page load
        View::composer('*', function ($view) {
            $settings = Cache::remember('system_settings', 86400, function () {
                $data = \App\Models\AppSetting::first();
                return [
                    'app_name' => $data->app_name ?? 'HRIS Payroll',
                    'app_logo' => $data->app_logo ?? null,
                ];
            });
            $view->with('systemSettings', (object) $settings);
        });
    }
}
