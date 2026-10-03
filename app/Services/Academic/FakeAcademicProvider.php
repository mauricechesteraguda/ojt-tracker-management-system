<?php

namespace App\Services\Academic;

use App\Contracts\AcademicProvider;
use App\Exceptions\AcademicProviderException;
use App\Support\SessionTracer;

/* feature-10032026-Maurice: deterministic local-only provider implementation. */
class FakeAcademicProvider implements AcademicProvider
{
    public function profile($srCode, $password, $correlationId = null)
    {
        $started = microtime(true);
        $correlationId = $correlationId === null ? 'missing-correlation' : SessionTracer::id($correlationId);
        SessionTracer::enter('academic.profile', $correlationId, array('mode' => 'fake'));
        try {
            if ($srCode === 'DEMO-FAKE-timeout') {
                throw new AcademicProviderException('academic_provider_timeout');
            }
            if ($srCode === 'DEMO-FAKE-unavailable') {
                throw new AcademicProviderException('academic_provider_unavailable');
            }
            if ($srCode !== 'DEMO-STUDENT-001' || $password !== 'DemoOnly-Student-001!') {
                throw new AcademicProviderException('academic_credentials_invalid');
            }
            $result = array('profile' => array('sr_code' => $srCode, 'first_name' => 'Demo', 'last_name' => 'Student', 'middle_name' => 'Local'), 'photo' => array('url' => 'https://example.test/photo-placeholder.png'), 'enrollment' => array('schoolyear' => '2026-2027', 'semester' => 'FIRST', 'course_code' => 'BSCOSCI', 'campus' => 'MAIN', 'college_code' => 'CICS'), 'correlation_id' => $correlationId);
            SessionTracer::leave('academic.profile', $correlationId, $started, 'success');
            return $result;
        } catch (AcademicProviderException $exception) {
            SessionTracer::exception('academic.profile', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        }
    }

    public function schoolYears($correlationId = null)
    {
        return $this->catalog('schoolyears', array('2026-2027'), $correlationId);
    }

    public function semesters($correlationId = null)
    {
        return $this->catalog('semesters', array('FIRST', 'SECOND'), $correlationId);
    }

    public function enrollmentRecords($schoolYear, $semester, $srCode, $correlationId = null)
    {
        /* feature/fix-10032026-Maurice: catch (?Throwable $trace); fake enrollment is deterministic for synthetic authenticated users. */
        $started = microtime(true);
        $correlationId = $correlationId === null ? 'missing-correlation' : SessionTracer::id($correlationId);
        SessionTracer::enter('academic.enrollment', $correlationId, array('mode' => 'fake'));
        try {
            if ($srCode !== '') {
                $result = array(array('schoolyear' => $schoolYear, 'semester' => $semester, 'coursecode' => 'BSCOSCI', 'campus' => 'MAIN', 'collegecode' => 'CICS', 'middlename' => 'Local', 'firstname' => 'Demo', 'lastname' => 'Student'));
                SessionTracer::leave('academic.enrollment', $correlationId, $started, 'success');
                return $result;
            }
            SessionTracer::leave('academic.enrollment', $correlationId, $started, 'success');
            return array();
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.enrollment', $correlationId, $started, 'academic_provider_unexpected', $exception);
            throw $exception;
        }
    }

    public function majors($course, $correlationId = null)
    {
        return $this->catalog('majors', array(), $correlationId);
    }

    public function courses($college, $correlationId = null)
    {
        return $this->catalog('courses', array(array('code' => 'BSCOSCI', 'description' => 'Computer Science')), $correlationId);
    }

    public function colleges($correlationId = null)
    {
        return $this->catalog('colleges', array(array('code' => 'CICS', 'description' => 'Computing Studies')), $correlationId);
    }

    public function campuses($correlationId = null)
    {
        return $this->catalog('campuses', array(array('code' => 'MAIN', 'description' => 'Main Campus')), $correlationId);
    }

    protected function catalog($operation, array $result, $correlationId = null)
    {
        $correlationId = $correlationId === null ? 'missing-correlation' : SessionTracer::id($correlationId);
        $started = microtime(true);
        SessionTracer::enter('academic.' . $operation, $correlationId, array('mode' => 'fake'));
        SessionTracer::leave('academic.' . $operation, $correlationId, $started, 'success');
        return $result;
    }
}
