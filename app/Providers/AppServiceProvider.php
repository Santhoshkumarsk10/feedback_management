<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

use App\Models\Company;
use App\Models\Shift;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('companies')) {
                    $view->with('currentCompany', Company::current());
                }
            } catch (\Throwable $e) {
                // Ignore during migrations / early setup
            }
        });

        View::composer(['layouts.topbar', 'layouts.app'], function ($view) {
            try {
                if (auth()->check() && Schema::hasTable('shifts') && Schema::hasTable('visits')) {
                    $view->with('topbarShiftAlerts', Shift::getFeedbackCloseAlerts(30));
                }
            } catch (\Throwable $e) {
                // Ignore during early setup
            }
        });
    }
}
