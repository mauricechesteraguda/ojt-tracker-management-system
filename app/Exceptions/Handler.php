<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Support\ApiErrorNormalizer;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use App\Support\SessionTracer;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $started = microtime(true); $correlationId = SessionTracer::id($request->header('X-Correlation-ID')); SessionTracer::enter('exception.handler.render', $correlationId, array('resource_type' => 'api'));
        try {
            /* catch (?Throwable $trace contract */
            if ($request->expectsJson() || $request->is('api/*')) {
                $prepared = $this->prepareException($exception);
                $status = $this->isHttpException($prepared) ? (int) $prepared->getStatusCode() : ($prepared instanceof AuthenticationException ? 401 : ($prepared instanceof ModelNotFoundException ? 404 : ($prepared instanceof ValidationException ? 422 : 500)));
                /* fix-10042026-Maurice: preserve read-only API collection rejections as safe exact 405s. */
                $methodNotAllowed = $prepared instanceof MethodNotAllowedHttpException || $status === 405;
                if ($methodNotAllowed || in_array($status, array(401, 403, 404, 409, 422), true)) {
                    if ($methodNotAllowed) {
                        Log::warning('http_method_not_allowed', array('event' => 'http_method_not_allowed', 'category' => 'expected_http_405', 'operation' => 'exception.handler.render', 'status' => 405, 'correlation_id' => $correlationId, 'message_category' => 'method_not_allowed'));
                    }
                    $code = $methodNotAllowed ? 'method_not_allowed' : ($status === 422 ? 'validation_error' : ($status === 409 ? 'conflict' : ($status === 404 ? 'not_found' : ($status === 403 ? 'forbidden' : 'unauthenticated'))));
                    $result = ApiErrorNormalizer::response($request, $methodNotAllowed ? 405 : $status, $code);
                    SessionTracer::leave('exception.handler.render', $correlationId, $started, 'safe_api_error', array('resource_type' => 'api', 'status' => $status)); return $result;
                }
            }
            if ($this->isHttpException($exception)) {
                switch (intval($exception->getStatusCode())) {
                    // not found
                case 404:
                        $result = redirect()->route('home'); SessionTracer::leave('exception.handler.render', $correlationId, $started, 'redirect', array('resource_type' => 'api', 'status' => 404)); return $result;
                    break;
                    // internal error
                case 500:
                        $result = \Response::view('custom.500', array(), 500); SessionTracer::leave('exception.handler.render', $correlationId, $started, 'server_error', array('resource_type' => 'api', 'status' => 500)); return $result;
                    break;

                default:
                        $result = $this->renderHttpException($exception); SessionTracer::leave('exception.handler.render', $correlationId, $started, 'http_error', array('resource_type' => 'api', 'status' => (int) $exception->getStatusCode())); return $result;
                    break;
                }
            } else {
                $result = parent::render($request, $exception); SessionTracer::leave('exception.handler.render', $correlationId, $started, 'framework_error', array('resource_type' => 'api')); return $result;
            }
        } catch (\Throwable $throwable) {
            SessionTracer::exception('exception.handler.render', $correlationId, $started, 'handler_unexpected', $throwable); throw $throwable;
        }
    }
}
