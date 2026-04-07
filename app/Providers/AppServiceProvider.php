<?php

namespace App\Providers;

use App\Models\AppSetting;
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
        // Share AppSettings across all views
        View::composer('*', function ($view) {
            $view->with('systemSettings', AppSetting::first() ?: (object)['app_name' => 'HRIS Payroll', 'app_logo' => null]);
        });
    }
}
