<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

use App\Models\Company;

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

        Gate::before(function ($user, $ability) {
            if ($user->hasRole('superadmin') || $user->role === 'superadmin') {
                return true;
            }
        });

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
                if (auth()->check()) {
                    $pendingToday = 0;
                    $escalatedCount = 0;

                    if (Schema::hasTable('visits')) {
                        $pendingToday = \App\Models\Visit::whereDate('visit_date', today())->doesntHave('feedback')->count();
                    }

                    if (Schema::hasTable('feedbacks')) {
                        $escalatedCount = \App\Models\Feedback::where('overall_rating', '<=', 2)
                            ->whereDate('submitted_at', '>=', now()->subDays(7))
                            ->count();
                    }

                    $view->with([
                        'topbarShiftAlerts' => [],
                        'topbarPendingCount' => $pendingToday,
                        'topbarEscalatedCount' => $escalatedCount,
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignore during early setup
            }
        });
    }
}
