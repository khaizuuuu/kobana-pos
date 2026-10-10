<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale(config('app.locale', 'id'));

        Blade::directive('rupiah', function ($expression) {
            return "<?php echo format_rupiah($expression); ?>";
        });

        Blade::directive('tanggal', function ($expression) {
            return "<?php echo format_tanggal($expression); ?>";
        });

        // Gates
        \Illuminate\Support\Facades\Gate::define('admin', fn(\App\Models\User $user) => $user->isAdmin());
        \Illuminate\Support\Facades\Gate::define('manage-users', fn(\App\Models\User $user) => $user->isAdmin());
        \Illuminate\Support\Facades\Gate::define('manage-master', fn(\App\Models\User $user) => $user->isAdmin());
        \Illuminate\Support\Facades\Gate::define('manage-transactions', fn(\App\Models\User $user) => in_array($user->role, ['admin', 'kasir']));
        \Illuminate\Support\Facades\Gate::define('delete-transaction', fn(\App\Models\User $user) => $user->isAdmin());
        \Illuminate\Support\Facades\Gate::define('view-analytics', fn(\App\Models\User $user) => in_array($user->role, ['admin', 'owner']));
        \Illuminate\Support\Facades\Gate::define('run-fp-growth', fn(\App\Models\User $user) => in_array($user->role, ['admin', 'owner']));
        \Illuminate\Support\Facades\Gate::define('import-data', fn(\App\Models\User $user) => $user->isAdmin());
    }
}
