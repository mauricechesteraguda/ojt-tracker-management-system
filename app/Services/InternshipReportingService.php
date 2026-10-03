<?php

namespace App\Services;

use App\Internship;
use App\Support\SessionTracer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/* feature/fix-10032026-Maurice: shared authorized POST reporting query and pagination. */
class InternshipReportingService
{
    private $filters = array('campus', 'schoolyear', 'semester', 'college', 'course');

    public function validateFilters(Request $request)
    {
        $started = microtime(true); $cid = SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('internship.reporting.validate', $cid, array('resource_type' => 'internship'));
        try { /* catch (?Throwable $trace contract */
            $errors = array(); $result = array();
            foreach ($this->filters as $key) {
                $value = $request->input($key);
                if (!is_string($value) || $value === '' || strlen($value) > 100 || !preg_match('/^[A-Za-z0-9 _-]+$/D', $value)) { $errors[$key] = 'The '.$key.' filter is invalid.'; }
                $result[$key] = $value;
            }
            foreach (array('page' => 1, 'per_page' => 15) as $key => $default) {
                $value = $request->input($key, $default);
                if (!is_scalar($value) || filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1 || ($key === 'per_page' && (int) $value > 100)) { $errors[$key] = 'The '.$key.' value is invalid.'; }
                $result[$key] = (int) $value;
            }
            if ($errors) { throw ValidationException::withMessages($errors); }
            SessionTracer::leave('internship.reporting.validate', $cid, $started, 'success', array('resource_type' => 'internship')); return $result;
        } catch (ValidationException $exception) { SessionTracer::leave('internship.reporting.validate', $cid, $started, 'invalid', array('resource_type' => 'internship')); throw $exception;
        } catch (\Throwable $exception) { SessionTracer::exception('internship.reporting.validate', $cid, $started, 'reporting_validation_unexpected', $exception); throw $exception; }
    }

    public function query(Request $request, array $filters)
    {
        $started = microtime(true); $cid = SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('internship.reporting.query', $cid, array('resource_type' => 'internship'));
        try { /* catch (?Throwable $trace contract */
            $user = $request->user();
            $query = Internship::with(array('user', 'company'))->where('internships.is_deleted', '0')->where('campus', $filters['campus'])->where('schoolyear', $filters['schoolyear'])->where('semester', $filters['semester'])->where('college_code', $filters['college'])->where('course_code', $filters['course']);
            if (!$user || !in_array((string) $user->role, array('student', 'coordinator', 'superuser'), true)) { $query->where('internships.user_id', -1); }
            elseif ($user->role === 'student') { $query->where('internships.user_id', (int) $user->id); }
            $result = $query->join('users', 'users.id', '=', 'internships.user_id')->orderBy('users.first_name', 'asc')->orderBy('users.last_name', 'asc')->orderBy('internships.id', 'asc')->select('internships.*')->paginate($filters['per_page'], array('internships.*'), 'page', $filters['page']);
            SessionTracer::leave('internship.reporting.query', $cid, $started, 'success', array('resource_type' => 'internship', 'status' => $result->total())); return $result;
        } catch (\Throwable $exception) { SessionTracer::exception('internship.reporting.query', $cid, $started, 'reporting_query_unexpected', $exception); throw $exception; }
    }

    public function records($paginator, Request $request)
    {
        $started = microtime(true); $cid = SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('internship.reporting.records', $cid, array('resource_type' => 'internship'));
        try { /* catch (?Throwable $trace contract */
            $records = array();
            foreach ($paginator->getCollection() as $internship) {
                $user = $internship->user; $company = $internship->company;
                $records[] = array('id' => (int) $internship->id, 'first_name' => (string) $user->first_name, 'last_name' => (string) $user->last_name, 'sr_code' => 'SR-'.(int) $user->id, 'company' => (string) optional($company)->name, 'start_date' => (string) $internship->start_date, 'end_date' => (string) $internship->end_date, 'approval' => (int) $internship->is_approved ? 'approved' : 'pending', 'status' => (string) $internship->status, 'campus' => (string) $internship->campus, 'schoolyear' => (string) $internship->schoolyear, 'semester' => (string) $internship->semester, 'college' => (string) $internship->college_code, 'course' => (string) $internship->course_code);
            }
            SessionTracer::leave('internship.reporting.records', $cid, $started, 'success', array('resource_type' => 'internship', 'status' => count($records))); return $records;
        } catch (\Throwable $exception) { SessionTracer::exception('internship.reporting.records', $cid, $started, 'reporting_transform_unexpected', $exception); throw $exception; }
    }
}
