<?php
use App\BusinessPermit;
use App\BusinessTransaction;
use App\TricyclePermit;
use App\TricycleTransaction;
use App\BoatPermit;
use App\BoatTransaction;
use App\Medical;
use App\Food;
use App\Wet;
use App\FireInspection;
use App\FireTransaction;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if(Auth::check()) {
        $user = \Auth::user();
        return view('home',['user'=>$user]);
    } else {
        return view('auth/login');
    }
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
