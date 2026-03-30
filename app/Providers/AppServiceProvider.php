<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Role;
use App\Observers\CompanyObserver;
use App\Observers\RoleObserver;
use App\Repositories\CurrencyRepository;
use App\Repositories\CurrencyRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\CurrencyRateRepositoryInterface::class,
            \App\Repositories\CurrencyRateRepository::class,

        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Company::observe(CompanyObserver::class);
        Role::observe(RoleObserver::class);
    }
}
