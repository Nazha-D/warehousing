<?php

namespace App\Providers;

use App\Repositories\CurrencyRepository;
use App\Repositories\CurrencyRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            CurrencyRepositoryInterface::class,
            CurrencyRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
