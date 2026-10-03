<?php

namespace App\Services\Academic;

use App\Contracts\AcademicProvider;
use App\Exceptions\AcademicProviderException;
use App\Support\SessionTracer;

/* feature-10032026-Maurice: environment-only provider selection, fail closed. */
class AcademicProviderManager implements AcademicProvider
{
    protected $provider;

    public function __construct(FakeAcademicProvider $fake, RealAcademicProvider $real)
    {
        $started = microtime(true);
        $correlationId = 'startup';
        SessionTracer::enter('academic.manager.construct', $correlationId, array('mode' => config('academic.mode', 'fake')));
        $mode = config('academic.mode', 'fake');
        if ($mode === 'fake') {
            $this->provider = $fake;
        } elseif ($mode === 'real') {
            $this->provider = $real;
        } else {
            $exception = new AcademicProviderException('academic_provider_unavailable');
            SessionTracer::exception('academic.manager.construct', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        }
        SessionTracer::leave('academic.manager.construct', $correlationId, $started, 'success');
    }

    public function profile($srCode, $password, $correlationId = null)
    {
        $arguments = array($srCode, $password);
        return $this->delegate('profile', $arguments, $correlationId);
    }

    public function schoolYears($correlationId = null)
    {
        return $this->delegate('schoolYears', array(), $correlationId);
    }

    public function semesters($correlationId = null)
    {
        return $this->delegate('semesters', array(), $correlationId);
    }

    public function enrollmentRecords($schoolYear, $semester, $srCode, $correlationId = null)
    {
        return $this->delegate('enrollmentRecords', array($schoolYear, $semester, $srCode), $correlationId);
    }

    public function majors($course, $correlationId = null)
    {
        return $this->delegate('majors', array($course), $correlationId);
    }

    public function courses($college, $correlationId = null)
    {
        return $this->delegate('courses', array($college), $correlationId);
    }

    public function colleges($correlationId = null)
    {
        return $this->delegate('colleges', array(), $correlationId);
    }

    public function campuses($correlationId = null)
    {
        return $this->delegate('campuses', array(), $correlationId);
    }

    protected function delegate($method, array $arguments, $correlationId = null)
    {
        $correlationId = $correlationId === null ? 'missing-correlation' : SessionTracer::id($correlationId);
        $started = microtime(true);
        SessionTracer::enter('academic.manager.' . $method, $correlationId, array('mode' => config('academic.mode')));
        try {
            $callArguments = $arguments;
            $callArguments[] = $correlationId;
            $result = call_user_func_array(array($this->provider, $method), $callArguments);
            SessionTracer::leave('academic.manager.' . $method, $correlationId, $started, 'success');
            return $result;
        } catch (AcademicProviderException $exception) {
            SessionTracer::exception('academic.manager.' . $method, $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.manager.' . $method, $correlationId, $started, 'academic_provider_unexpected', $exception);
            throw new AcademicProviderException('academic_provider_unavailable', 'Academic provider unavailable', 0, $exception);
        }
    }
}
