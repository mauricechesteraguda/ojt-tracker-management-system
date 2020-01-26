<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\batsu_api;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();
        // $api = new batsu_api('02f56c7e26b713ab877cff2fc5c3ea8a');
        // $schoolyears = json_decode($api->fetch_schoolyear(),true);
        // $semesters = json_decode($api->fetch_semester(),true);
        // krsort($semesters);
        // foreach ($schoolyears as $sy) {
        //     # code...
        //     foreach ($semesters as $sem) {
        //         $user_enrollment_record = json_decode($api->fetch_enrollment_records($sy,$sem,'18-04852'),true);
        //         if ($user_enrollment_record) {
        //             break;
        //         }
        //     }

        // }
        

        
        return view('home',['user'=>$user]);
    }
}
