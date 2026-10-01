<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Writing\HandwritingExtractionService;
use App\Services\Writing\FakeHandwritingExtractionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            HandwritingExtractionService::class,
            FakeHandwritingExtractionService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
