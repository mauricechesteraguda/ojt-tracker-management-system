<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\SessionTracer;

class HomeController extends Controller
{
    /* fix-10032026-Maurice: remove legacy credential-bearing provider code from dashboard. */
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        SessionTracer::enter('home.controller.construct', 'startup', array('mode' => 'application'));
        try {
        $this->middleware('auth');
        SessionTracer::leave('home.controller.construct', 'startup', microtime(true), 'success');
        } catch (\Throwable $exception) {
            SessionTracer::exception('home.controller.construct', 'startup', microtime(true), 'home_unexpected', $exception);
            throw $exception;
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $started = microtime(true);
        $correlationId = SessionTracer::id();
        SessionTracer::enter('home.index', $correlationId, array('mode' => 'application'));
        try {
        $user = \Auth::user();
        $view = view('home',['user'=>$user]);
        SessionTracer::leave('home.index', $correlationId, $started, 'success');
        return $view;
        } catch (\Throwable $exception) {
            SessionTracer::exception('home.index', $correlationId, $started, 'home_unexpected', $exception);
            throw $exception;
        }
    }
}
