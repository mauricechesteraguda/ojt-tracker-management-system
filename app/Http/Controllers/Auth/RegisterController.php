<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

use App\Classes\batsu_api;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
            return Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'sr_code' => ['required','string','max:255','unique:users'],
                'contact_no' => ['string', 'max:255'],
                'parent' => ['string', 'max:255'],
                'parent_contact_no' => ['string', 'max:255'],
                'current_schoolyear' => ['string', 'max:255'],
                'current_course_code' => ['string', 'max:255']
            ]);
    }

    /**
     *
     * Override Trait RegistersUsers : vendor/laravel/framework/src/Illuminate/Foundation/Auth/RegistersUsers.php
     *
    */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        $api = new batsu_api('02f56c7e26b713ab877cff2fc5c3ea8a');
        $user = json_decode($api->fetch_student_profile( $request->input('sr_code')),true);

        if (!$user) {
            $validator->getMessageBag()->add('sr_code', 'Incorrect code.');
            return redirect('/register')
            ->withErrors($validator,'sr_code')
            ->withInput();
        }

        $data = json_decode($api->authenticate_student($request->input('sr_code'),$request->input('password')),true);

        if(empty($data))
        {
            $validator->getMessageBag()->add('password', 'Incorrect sr code/password.');
            return redirect('/register')
            ->withErrors($validator,'password')
            ->withInput();
        }

        if ($validator->fails()) {
            return redirect('/register')
            ->withErrors($validator)
            ->withInput();
        }

        $created_user = $this->create($request->all());
        \Auth::login($created_user);

        return redirect($this->redirectPath());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $api = new batsu_api('02f56c7e26b713ab877cff2fc5c3ea8a');
        $schoolyears = json_decode($api->fetch_schoolyear(),true);
        $semesters = json_decode($api->fetch_semester(),true);
        krsort($semesters);
        foreach ($schoolyears as $sy) {
            # code...
            foreach ($semesters as $sem) {
                $user_enrollment_record = json_decode($api->fetch_enrollment_records($sy,$sem,$data['sr_code']),true);
                if ($user_enrollment_record) {
                    break 2;
                }
            }

        }

        $auth_data = json_decode($api->authenticate_student($data['sr_code'],$data['password']),true);

        $photo_url = '';
        if (!empty($auth_data)) {
            $token = $auth_data[0]['token'];
            $photo_url = $api->fetch_student_photo_url($data['sr_code'],$token);
        }
                
            return User::create([
            'name' => $user_enrollment_record[0]['middlename'],
            'first_name' => $user_enrollment_record[0]['firstname'],
            'last_name' => $user_enrollment_record[0]['lastname'],
            'current_schoolyear' => $user_enrollment_record[0]['schoolyear'],
            'current_course_code' => $user_enrollment_record[0]['coursecode'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'sr_code' => $data['sr_code'],
            'contact_no' => $data['contact_no'],
            'parent' => $data['parent'],
            'parent_contact_no' => $data['parent_contact_no'],
            'photo_url' => $photo_url,
            
        ]);
    }

    
}
