<?php

namespace App\Services\Academic;

use App\Contracts\AcademicProvider;
use App\Exceptions\AcademicProviderException;
use App\Support\SessionTracer;

/* fix-10032026-Maurice: explicit, bounded, fail-closed real adapter. */
class RealAcademicProvider implements AcademicProvider
{
    protected $config;

    public function __construct()
    {
        $started = microtime(true);
        SessionTracer::enter('academic.real.construct', 'startup', array('mode' => 'real'));
        try {
        $this->config = config('academic', array());
        SessionTracer::leave('academic.real.construct', 'startup', $started, 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.real.construct', 'startup', $started, 'academic_provider_unexpected', $exception);
            throw $exception;
        }
    }

    public function profile($srCode, $password, $correlationId = null)
    {
        $started = microtime(true); $correlationId = $correlationId === null ? 'missing-correlation' : SessionTracer::id($correlationId); SessionTracer::enter('academic.real.profile', $correlationId, array('mode' => 'real')); try {
        $payload = array('sr_code' => $srCode, 'password' => $password);
        $result = $this->request('profile', $payload, $correlationId); SessionTracer::leave('academic.real.profile', $correlationId, $started, 'success'); return $result;
        } catch (AcademicProviderException $exception) { SessionTracer::exception('academic.real.profile', $correlationId, $started, $exception->category(), $exception); throw $exception; } catch (\Throwable $exception) { SessionTracer::exception('academic.real.profile', $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }
    }

    public function schoolYears($correlationId = null)
    {
        return $this->adapterCall('schoolyears', array(), $correlationId);
    }

    public function semesters($correlationId = null)
    {
        return $this->adapterCall('semesters', array(), $correlationId);
    }

    public function enrollmentRecords($schoolYear, $semester, $srCode, $correlationId = null)
    {
        return $this->adapterCall('enrollment', array('schoolyear' => $schoolYear, 'semester' => $semester, 'sr_code' => $srCode), $correlationId);
    }

    public function majors($course, $correlationId = null)
    {
        return $this->adapterCall('majors', array('course' => $course), $correlationId);
    }

    public function courses($college, $correlationId = null)
    {
        return $this->adapterCall('courses', array('college' => $college), $correlationId);
    }

    public function colleges($correlationId = null)
    {
        return $this->adapterCall('colleges', array(), $correlationId);
    }

    public function campuses($correlationId = null)
    {
        return $this->adapterCall('campuses', array(), $correlationId);
    }

    protected function adapterCall($operation, array $payload, $correlationId = null)
    {
        $started = microtime(true); $correlationId = $correlationId === null ? 'missing-correlation' : SessionTracer::id($correlationId); SessionTracer::enter('academic.real.' . $operation, $correlationId, array('mode' => 'real'));
        try { $result = $this->request($operation, $payload, $correlationId); SessionTracer::leave('academic.real.' . $operation, $correlationId, $started, 'success'); return $result; } catch (AcademicProviderException $exception) { SessionTracer::exception('academic.real.' . $operation, $correlationId, $started, $exception->category(), $exception); throw $exception; } catch (\Throwable $exception) { SessionTracer::exception('academic.real.' . $operation, $correlationId, $started, 'academic_provider_unexpected', $exception); throw $exception; }
    }

    protected function request($operation, array $payload, $correlationId = null)
    {
        $started = microtime(true);
        $correlationId = $correlationId === null ? 'missing-correlation' : SessionTracer::id($correlationId);
        SessionTracer::enter('academic.' . $operation, $correlationId, array('mode' => 'real'));
        try {
            $baseUrl = isset($this->config['base_url']) ? trim($this->config['base_url']) : '';
            $key = isset($this->config['key']) ? trim($this->config['key']) : '';
            if ($baseUrl === '' || $key === '' || !$this->allowedUrl($baseUrl, $correlationId)) {
                throw new AcademicProviderException('academic_provider_unavailable');
            }
            $body = json_encode(array('operation' => $operation, 'payload' => $payload));
            $response = $this->transport($baseUrl, $key, $body, $correlationId);
            $decoded = json_decode($response, true);
            $decoded = $this->normalize($operation, $decoded, $correlationId);
            SessionTracer::leave('academic.' . $operation, $correlationId, $started, 'success');
            return $decoded;
        } catch (AcademicProviderException $exception) {
            SessionTracer::exception('academic.' . $operation, $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        }
    }

    protected function transport($baseUrl, $key, $body, $correlationId)
    {
        $started = microtime(true);
        SessionTracer::enter('academic.transport', $correlationId, array('mode' => 'real'));
        try {
        if (!function_exists('curl_init')) {
            $exception = new AcademicProviderException('academic_provider_unavailable');
            SessionTracer::exception('academic.transport', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        }
        $handle = curl_init($baseUrl);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $key));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, (int) ($this->config['connect_timeout'] ?: 2));
        curl_setopt($handle, CURLOPT_TIMEOUT, (int) ($this->config['timeout'] ?: 5));
        $response = curl_exec($handle);
        $error = curl_errno($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        if ($response === false || $error) {
            $exception = new AcademicProviderException('academic_provider_timeout');
            SessionTracer::exception('academic.transport', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        }
        if ($status < 200 || $status >= 300) {
            $exception = new AcademicProviderException('academic_provider_unavailable');
            SessionTracer::exception('academic.transport', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        }
        SessionTracer::leave('academic.transport', $correlationId, $started, 'success');
        return $response;
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.transport', $correlationId, $started, $exception instanceof AcademicProviderException ? $exception->category() : 'academic_provider_unexpected', $exception);
            throw $exception;
        }
    }

    protected function allowedUrl($url, $correlationId)
    {
        $started = microtime(true);
        SessionTracer::enter('academic.url_policy', $correlationId, array('mode' => 'real'));
        try {
        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || strtolower($parts['scheme']) === 'https') {
            $allowed = !empty($parts['host']);
            SessionTracer::leave('academic.url_policy', $correlationId, $started, $allowed ? 'success' : 'unavailable');
            return $allowed;
        }
        $host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $local = in_array($host, array('127.0.0.1', '::1', 'localhost', 'host.docker.internal'), true);
        $allowed = config('academic.allow_local', false) && app()->environment('local', 'testing') && $local;
        SessionTracer::leave('academic.url_policy', $correlationId, $started, $allowed ? 'success' : 'unavailable');
        return $allowed;
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.url_policy', $correlationId, $started, 'academic_provider_unexpected', $exception);
            throw $exception;
        }
    }

    protected function normalize($operation, $decoded, $correlationId)
    {
        $started = microtime(true);
        SessionTracer::enter('academic.normalize', $correlationId, array('mode' => 'real'));
        try {
        if (!is_array($decoded)) {
            throw new AcademicProviderException('academic_provider_malformed');
        }
        if ($operation === 'profile') {
            $result = $this->normalizeProfile($decoded, $correlationId);
            SessionTracer::leave('academic.normalize', $correlationId, $started, 'success');
            return $result;
        }
        if (!array_key_exists('data', $decoded) || !is_array($decoded['data'])) {
            throw new AcademicProviderException('academic_provider_malformed');
        }
        $result = array();
        foreach ($decoded['data'] as $item) {
            if (is_string($item)) {
                $result[] = $item;
            } elseif (is_array($item) && isset($item['code'], $item['description']) && count(array_diff(array_keys($item), array('code', 'description'))) === 0) {
                $result[] = array('code' => (string) $item['code'], 'description' => (string) $item['description']);
            } else {
                throw new AcademicProviderException('academic_provider_malformed');
            }
        }
        SessionTracer::leave('academic.normalize', $correlationId, $started, 'success');
        return $result;
        } catch (AcademicProviderException $exception) {
            SessionTracer::exception('academic.normalize', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.normalize', $correlationId, $started, 'academic_provider_unexpected', $exception);
            throw $exception;
        }
    }

    protected function normalizeProfile($decoded, $correlationId)
    {
        $started = microtime(true);
        SessionTracer::enter('academic.normalize.profile', $correlationId, array('mode' => 'real'));
        try {
        $allowed = array('profile', 'photo', 'enrollment');
        if (count(array_diff(array_keys($decoded), $allowed)) !== 0 || !isset($decoded['profile'], $decoded['photo'], $decoded['enrollment'])) {
            throw new AcademicProviderException('academic_provider_malformed');
        }
        $profile = $decoded['profile'];
        $photo = $decoded['photo'];
        $enrollment = $decoded['enrollment'];
        $profileKeys = array('sr_code', 'first_name', 'last_name', 'middle_name');
        $enrollmentKeys = array('schoolyear', 'semester', 'course_code', 'campus', 'college_code');
        if (!is_array($profile) || !is_array($photo) || !is_array($enrollment) || count(array_diff(array_keys($profile), $profileKeys)) !== 0 || count(array_diff(array_keys($photo), array('url'))) !== 0 || count(array_diff(array_keys($enrollment), $enrollmentKeys)) !== 0 || !isset($profile['sr_code'], $profile['first_name'], $profile['last_name'], $photo['url'])) {
            throw new AcademicProviderException('academic_provider_malformed');
        }
        $result = array('profile' => array('sr_code' => (string) $profile['sr_code'], 'first_name' => (string) $profile['first_name'], 'last_name' => (string) $profile['last_name'], 'middle_name' => isset($profile['middle_name']) ? (string) $profile['middle_name'] : ''), 'photo' => array('url' => (string) $photo['url']), 'enrollment' => array('schoolyear' => isset($enrollment['schoolyear']) ? (string) $enrollment['schoolyear'] : '', 'semester' => isset($enrollment['semester']) ? (string) $enrollment['semester'] : '', 'course_code' => isset($enrollment['course_code']) ? (string) $enrollment['course_code'] : '', 'campus' => isset($enrollment['campus']) ? (string) $enrollment['campus'] : '', 'college_code' => isset($enrollment['college_code']) ? (string) $enrollment['college_code'] : ''), 'correlation_id' => $correlationId);
        SessionTracer::leave('academic.normalize.profile', $correlationId, $started, 'success');
        return $result;
        } catch (AcademicProviderException $exception) {
            SessionTracer::exception('academic.normalize.profile', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.normalize.profile', $correlationId, $started, 'academic_provider_unexpected', $exception);
            throw $exception;
        }
    }
}
