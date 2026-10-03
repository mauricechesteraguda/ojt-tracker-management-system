<?php

namespace App\Providers;

use App\Contracts\AcademicProvider;
use App\Services\Academic\AcademicProviderManager;
use App\Support\SessionTracer;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $started = microtime(true);
        $correlationId = 'startup';
        SessionTracer::enter('app.boot', $correlationId, array());
        try {
        //
        Schema::defaultStringLength(191);
        SessionTracer::leave('app.boot', $correlationId, $started, 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('app.boot', $correlationId, $started, 'application_unexpected', $exception);
            throw $exception;
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $started = microtime(true);
        $correlationId = 'startup';
        SessionTracer::enter('app.register', $correlationId, array());
        try {
        // fix-10032026-Maurice: bind the interface to a class, never a request-selected closure.
        $this->app->bind(AcademicProviderManager::class, AcademicProviderManager::class);
        $this->app->bind(AcademicProvider::class, AcademicProviderManager::class);
        SessionTracer::leave('app.register', $correlationId, $started, 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('app.register', $correlationId, $started, 'application_unexpected', $exception);
            throw $exception;
        }
    }
}
