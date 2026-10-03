<?php

namespace App\Http\Controllers;

use App\Contracts\AcademicProvider;
use App\Exceptions\AcademicProviderException;
use App\Support\SessionTracer;
use Illuminate\Http\Request;

/* feature-10032026-Maurice: unauthenticated, body-only academic profile endpoint. */
class AcademicProfileController extends Controller
{
    protected $provider;

    public function __construct(AcademicProvider $provider)
    {
        SessionTracer::enter('academic.profile.controller.construct', 'startup', array('mode' => config('academic.mode')));
        try {
        $this->provider = $provider;
        SessionTracer::leave('academic.profile.controller.construct', 'startup', microtime(true), 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.profile.controller.construct', 'startup', microtime(true), 'controller_unexpected', $exception);
            throw $exception;
        }
    }

    public function profile(Request $request)
    {
        $correlationId = SessionTracer::id($request->header('X-Correlation-ID'));
        $started = microtime(true);
        SessionTracer::enter('academic.profile.http', $correlationId, array('mode' => config('academic.mode')));
        $payload = $request->post();
        $query = $request->query();
        if (array_key_exists('sr_code', $query) || array_key_exists('password', $query)) {
            SessionTracer::leave('academic.profile.http', $correlationId, $started, 'validation');
            return response()->json(array('error' => array('code' => 'validation_failed', 'message' => 'Credentials must be supplied in the request body.'), 'correlation_id' => $correlationId), 422);
        }
        $validator = \Validator::make($payload, array('sr_code' => 'required|string|max:255', 'password' => 'required|string|max:255'));
        if ($validator->fails()) {
            SessionTracer::leave('academic.profile.http', $correlationId, $started, 'validation');
            return response()->json(array('error' => array('code' => 'validation_failed', 'message' => 'The supplied credentials are invalid.'), 'correlation_id' => $correlationId), 422);
        }
        try {
            $result = $this->provider->profile($payload['sr_code'], $payload['password'], $correlationId);
            if (!is_array($result) || !isset($result['correlation_id']) || $result['correlation_id'] !== $correlationId) {
                throw new AcademicProviderException('academic_provider_malformed');
            }
            SessionTracer::leave('academic.profile.http', $correlationId, $started, 'success');
            return response()->json($result, 200);
        } catch (AcademicProviderException $exception) {
            $status = $exception->category() === 'academic_credentials_invalid' ? 422 : 503;
            SessionTracer::exception('academic.profile.http', $correlationId, $started, $exception->category(), $exception);
            return response()->json(array('error' => array('code' => $exception->category(), 'message' => 'Academic profile is unavailable.'), 'correlation_id' => $correlationId), $status);
        } catch (\Throwable $exception) {
            SessionTracer::exception('academic.profile.http', $correlationId, $started, 'academic_provider_unexpected', $exception);
            return response()->json(array('error' => array('code' => 'academic_provider_unavailable', 'message' => 'Academic profile is unavailable.'), 'correlation_id' => $correlationId), 503);
        }
    }
}
