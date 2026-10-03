<?php

namespace App\Providers;

use App\Contracts\AcademicProvider;
use App\Services\Academic\AcademicProviderManager;
use App\Services\Academic\FakeAcademicProvider;
use App\Services\Academic\RealAcademicProvider;
use Illuminate\Support\ServiceProvider;
use App\Support\SessionTracer;

/* feature-10032026-Maurice: class-to-class provider bindings. */
class AcademicProviderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $started = microtime(true);
        $correlationId = 'startup';
        SessionTracer::enter('academic.provider.register', $correlationId, array());
        try {
        $this->app->bind(FakeAcademicProvider::class, FakeAcademicProvider::class);
        $this->app->bind(RealAcademicProvider::class, RealAcademicProvider::class);
        SessionTracer::leave('academic.provider.register', $correlationId, $started, 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.provider.register', $correlationId, $started, 'application_unexpected', $exception);
            throw $exception;
        }
    }
}
