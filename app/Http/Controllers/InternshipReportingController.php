<?php

namespace App\Http\Controllers;

use App\Services\InternshipReportingService;
use App\Support\ApiErrorNormalizer;
use App\Support\SessionTracer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/* feature/fix-10032026-Maurice: authenticated POST JSON/PDF actions share one service and page. */
class InternshipReportingController extends Controller
{
    protected $reporting;

    public function __construct(InternshipReportingService $reporting)
    {
        $started = microtime(true); $cid = SessionTracer::id(); SessionTracer::enter('internship.reporting.construct', $cid, array('resource_type' => 'internship'));
        try { /* catch (?Throwable $trace contract */ $this->reporting = $reporting; SessionTracer::leave('internship.reporting.construct', $cid, $started, 'success', array('resource_type' => 'internship')); }
        catch (\Throwable $exception) { SessionTracer::exception('internship.reporting.construct', $cid, $started, 'reporting_construct_unexpected', $exception); throw $exception; }
    }

    public function index(Request $request)
    {
        $started = microtime(true); $cid = SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('internship.reporting.index', $cid, array('resource_type' => 'internship'));
        try { /* catch (?Throwable $trace contract */
            $filters = $this->reporting->validateFilters($request); $paginator = $this->reporting->query($request, $filters); $data = $this->reporting->records($paginator, $request);
            $payload = array('data' => $data, 'meta' => array('current_page' => $paginator->currentPage(), 'from' => $paginator->firstItem(), 'last_page' => $paginator->lastPage(), 'per_page' => $paginator->perPage(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()), 'correlation_id' => $cid);
            SessionTracer::leave('internship.reporting.index', $cid, $started, 'success', array('resource_type' => 'internship', 'status' => $paginator->total())); Log::info('internship_reporting', array('event' => 'internship_reporting', 'component' => 'reporting', 'operation' => 'index', 'correlation_id' => $cid, 'status' => 'success')); return response()->json($payload, 200);
        } catch (ValidationException $exception) { SessionTracer::leave('internship.reporting.index', $cid, $started, 'invalid', array('resource_type' => 'internship')); return ApiErrorNormalizer::jsonResponse(array('message' => 'The given data was invalid.', 'errors' => $exception->errors(), 'correlation_id' => $cid), 422);
        } catch (\Throwable $exception) { SessionTracer::exception('internship.reporting.index', $cid, $started, 'reporting_unexpected', $exception); return ApiErrorNormalizer::response($request, 503, 'reporting_unavailable', $cid); }
    }

    public function pdf(Request $request)
    {
        $started = microtime(true); $cid = SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('internship.reporting.pdf', $cid, array('resource_type' => 'internship'));
        try { /* catch (?Throwable $trace contract */
            $filters = $this->reporting->validateFilters($request); $paginator = $this->reporting->query($request, $filters); $records = $this->reporting->records($paginator, $request); $pdf = app('dompdf.wrapper'); $pdf->getDomPDF()->set_option('isPhpEnabled', false)->set_paper('legal', 'landscape'); $pdf->loadView('pdf_view', array('records' => $records, 'filters' => $filters)); $response = $pdf->stream('internships.pdf'); $response->header('X-Reporting-Filter', json_encode($filters)); $response->header('X-Correlation-ID', $cid); SessionTracer::leave('internship.reporting.pdf', $cid, $started, 'success', array('resource_type' => 'internship', 'status' => $paginator->total())); Log::info('internship_reporting', array('event' => 'internship_reporting', 'component' => 'reporting', 'operation' => 'pdf', 'correlation_id' => $cid, 'status' => 'success')); return $response;
        } catch (ValidationException $exception) { SessionTracer::leave('internship.reporting.pdf', $cid, $started, 'invalid', array('resource_type' => 'internship')); return ApiErrorNormalizer::jsonResponse(array('message' => 'The given data was invalid.', 'errors' => $exception->errors(), 'correlation_id' => $cid), 422);
        } catch (\Throwable $exception) { SessionTracer::exception('internship.reporting.pdf', $cid, $started, 'reporting_render_unexpected', $exception); return ApiErrorNormalizer::response($request, 503, 'reporting_unavailable', $cid); }
    }
}
