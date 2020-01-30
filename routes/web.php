<?php

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



Route::get('/login', function () {
    
    return view('auth/login');

});

Route::get('/register', function () {
    
    return view('auth/register');

});

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
Route::get('/internships/print-pdf', [ 'as' => 'internship.printpdf', 'uses' => 'InternshipController@printPDF']);
