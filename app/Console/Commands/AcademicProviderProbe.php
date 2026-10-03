<?php

namespace App\Console\Commands;

use App\Contracts\AcademicProvider;
use App\Exceptions\AcademicProviderException;
use Illuminate\Console\Command;
use App\Support\SessionTracer;

/* feature-10032026-Maurice: local operational probe exposes safe status only. */
class AcademicProviderProbe extends Command
{
    protected $signature = 'academic:provider-probe {--sr-code=} {--password=} {--correlation-id=}';
    protected $description = 'Probe the configured academic provider without exposing credentials.';

    public function handle(AcademicProvider $provider)
    {
        $correlationId = SessionTracer::id($this->option('correlation-id'));
        $started = microtime(true);
        SessionTracer::enter('academic.provider.probe', $correlationId, array('mode' => config('academic.mode')));
        try {
            $result = $provider->profile($this->option('sr-code'), $this->option('password'), $correlationId);
            $this->line(json_encode(array('status' => 200, 'correlation_id' => $correlationId)));
            SessionTracer::leave('academic.provider.probe', $correlationId, $started, 'success');
            return 0;
        } catch (AcademicProviderException $exception) {
            SessionTracer::exception('academic.provider.probe', $correlationId, $started, $exception->category(), $exception);
            $this->line(json_encode(array('status' => 503, 'error' => $exception->category())));
            return 1;
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.provider.probe', $correlationId, $started, 'academic_provider_unexpected', $exception);
            $this->line(json_encode(array('status' => 503, 'error' => 'academic_provider_unavailable')));
            return 1;
        }
    }
}
