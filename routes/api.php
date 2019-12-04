<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users', 'UserController@index');
Route::get('/users/{id}', 'UserController@show');
Route::post('/users/{id}', 'UserController@update');
Route::post('/users', 'UserController@store');
Route::delete('/users/{id}', 'UserController@delete');
Route::get('/users/search/{value}', 'UserController@search');

Route::get('/companies', 'CompanyController@index');
Route::get('/companies/{id}', 'CompanyController@show');
Route::post('/companies/{id}', 'CompanyController@update');
Route::post('/companies', 'CompanyController@store');
Route::delete('/companies/{id}', 'CompanyController@delete');
Route::get('/companies/search/{value}', 'CompanyController@search');
