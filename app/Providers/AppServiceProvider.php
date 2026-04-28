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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Implicitly grant "Super Admin" role all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $capability) {
            return $user->hasRole('super-admin') || $user->hasRole('Super Admin') ? true : null;
        });

        \App\Models\Employee::observe(\App\Observers\AuditObserver::class);
        \App\Models\Payroll::observe(\App\Observers\AuditObserver::class);
        \App\Models\PayrollItem::observe(\App\Observers\AuditObserver::class);

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
