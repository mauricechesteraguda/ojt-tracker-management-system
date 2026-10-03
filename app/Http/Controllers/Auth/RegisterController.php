<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\AcademicProvider;
use App\Exceptions\AcademicProviderException;
use App\Http\Controllers\Controller;
use App\Support\SessionTracer;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/* fix-10032026-Maurice: registration consumes the provider boundary only. */
class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';
    protected $provider;
    protected $academicProfile;
    protected $correlationId;

    public function __construct(AcademicProvider $provider)
    {
        SessionTracer::enter('registration.controller.construct', 'startup', array('mode' => config('academic.mode')));
        try {
        $this->provider = $provider;
        $this->middleware('guest');
        SessionTracer::leave('registration.controller.construct', 'startup', microtime(true), 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('registration.controller.construct', 'startup', microtime(true), 'controller_unexpected', $exception);
            throw $exception;
        }
    }

    protected function validator(array $data)
    {
        $started = microtime(true);
        SessionTracer::enter('registration.validator', 'validation', array('mode' => config('academic.mode')));
        try {
        $validator = Validator::make($data, array(
            'name' => array('required', 'string', 'max:255'), 'first_name' => array('required', 'string', 'max:255'), 'last_name' => array('required', 'string', 'max:255'),
            'email' => array('required', 'string', 'email', 'max:255', 'unique:users'), 'password' => array('required', 'string', 'min:6', 'confirmed'),
            'sr_code' => array('required', 'string', 'max:255', 'unique:users'), 'contact_no' => array('string', 'max:255'), 'parent' => array('string', 'max:255'),
            'parent_contact_no' => array('string', 'max:255'), 'current_schoolyear' => array('string', 'max:255'), 'current_course_code' => array('string', 'max:255'),
        ));
        SessionTracer::leave('registration.validator', 'validation', $started, 'success');
        return $validator;
        } catch (\Throwable $exception) {
            SessionTracer::exception('registration.validator', 'validation', $started, 'validation_unexpected', $exception);
            throw $exception;
        }
    }

    public function register(Request $request)
    {
        $correlationId = SessionTracer::id($request->header('X-Correlation-ID'));
        $this->correlationId = $correlationId;
        $started = microtime(true);
        SessionTracer::enter('registration.academic_profile', $correlationId, array('mode' => config('academic.mode')));
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            SessionTracer::leave('registration.academic_profile', $correlationId, $started, 'validation');
            if ($request->expectsJson()) {
                return response()->json(array('error' => array('code' => 'validation_failed', 'message' => 'Registration fields are invalid.'), 'correlation_id' => $correlationId), 422);
            }
            return redirect('/register')->withErrors($validator)->withInput();
        }
        try {
            $this->academicProfile = $this->provider->profile($request->input('sr_code'), $request->input('password'), $correlationId);
        } catch (AcademicProviderException $exception) {
            $field = $exception->category() === 'academic_credentials_invalid' ? 'password' : 'sr_code';
            $validator->getMessageBag()->add($field, $exception->category() === 'academic_credentials_invalid' ? 'Incorrect sr code/password.' : 'Academic profile is temporarily unavailable.');
            SessionTracer::exception('registration.academic_profile', $correlationId, $started, $exception->category(), $exception);
            if ($request->expectsJson()) {
                return response()->json(array('error' => array('code' => $exception->category(), 'message' => 'Academic profile is unavailable.'), 'correlation_id' => $correlationId), $exception->category() === 'academic_credentials_invalid' ? 422 : 503);
            }
            return redirect('/register')->withErrors($validator)->withInput();
        } catch (\Throwable $exception) {
            SessionTracer::exception('registration.academic_profile', $correlationId, $started, 'academic_provider_unexpected', $exception);
            $validator->getMessageBag()->add('sr_code', 'Academic profile is temporarily unavailable.');
            if ($request->expectsJson()) {
                return response()->json(array('error' => array('code' => 'academic_provider_unavailable', 'message' => 'Academic profile is unavailable.'), 'correlation_id' => $correlationId), 503);
            }
            return redirect('/register')->withErrors($validator)->withInput();
        }
        $createdUser = $this->create($request->all());
        \Auth::login($createdUser);
        SessionTracer::leave('registration.academic_profile', $correlationId, $started, 'success');
        return redirect($this->redirectPath());
    }

    protected function create(array $data)
    {
        $started = microtime(true);
        $correlationId = $this->correlationId ?: 'missing-correlation';
        SessionTracer::enter('registration.create', $correlationId, array('mode' => config('academic.mode')));
        try {
        $profile = $this->academicProfile ?: $this->provider->profile($data['sr_code'], $data['password'], $correlationId);
        $enrollment = isset($profile['enrollment']) ? $profile['enrollment'] : array();
        $person = isset($profile['profile']) ? $profile['profile'] : array();
        $user = User::create(array(
            'name' => isset($person['middle_name']) ? $person['middle_name'] : '', 'first_name' => isset($person['first_name']) ? $person['first_name'] : $data['first_name'],
            'last_name' => isset($person['last_name']) ? $person['last_name'] : $data['last_name'], 'current_schoolyear' => isset($enrollment['schoolyear']) ? $enrollment['schoolyear'] : '',
            'current_course_code' => isset($enrollment['course_code']) ? $enrollment['course_code'] : '', 'email' => $data['email'], 'password' => Hash::make($data['password']),
            'role' => 'student', 'sr_code' => $data['sr_code'], 'contact_no' => isset($data['contact_no']) ? $data['contact_no'] : '',
            'parent' => isset($data['parent']) ? $data['parent'] : '', 'parent_contact_no' => isset($data['parent_contact_no']) ? $data['parent_contact_no'] : '',
            'photo_url' => isset($profile['photo']['url']) ? $profile['photo']['url'] : '', 'address' => isset($data['address']) ? $data['address'] : '',
        ));
        SessionTracer::leave('registration.create', $correlationId, $started, 'success');
        return $user;
        } catch (AcademicProviderException $exception) {
            SessionTracer::exception('registration.create', $correlationId, $started, $exception->category(), $exception);
            throw $exception;
        } catch (\Throwable $exception) {
            SessionTracer::exception('registration.create', $correlationId, $started, 'registration_unexpected', $exception);
            throw $exception;
        }
    }
}
