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
Route::middleware('auth:api')->group( function(){
    
Route::get('/users', 'UserController@index')->middleware('auth');
Route::get('/users/{id}', 'UserController@show')->middleware('auth');
Route::post('/users/{id}', 'UserController@update')->middleware('auth');
Route::post('/users', 'UserController@store')->middleware('auth');
Route::delete('/users/{id}', 'UserController@delete')->middleware('auth');
Route::get('/users/search/{value}', 'UserController@search')->middleware('auth');

Route::get('/companies', 'CompanyController@index')->middleware('auth');
Route::get('/companies/all', 'CompanyController@all')->middleware('auth');
Route::get('/companies/{id}', 'CompanyController@show')->middleware('auth');
Route::post('/companies/{id}', 'CompanyController@update')->middleware('auth');
Route::post('/companies', 'CompanyController@store')->middleware('auth');
Route::delete('/companies/{id}', 'CompanyController@delete')->middleware('auth');
Route::get('/companies/search/{value}', 'CompanyController@search')->middleware('auth');


Route::get('/internships', 'InternshipController@index')->middleware('auth');
Route::get('/internships/{id}', 'InternshipController@show')->middleware('auth');
Route::post('/internships/{id}', 'InternshipController@update')->middleware('auth');
Route::post('/internships', 'InternshipController@store')->middleware('auth');
Route::delete('/internships/{id}', 'InternshipController@delete')->middleware('auth');
Route::get('/internships/search/{value}', 'InternshipController@search')->middleware('auth');


Route::get('/descriptions', 'DescriptionController@index')->middleware('auth');
Route::get('/descriptions/internship/{id}', 'DescriptionController@by_internship_id')->middleware('auth');
// Route::get('/descriptions/{id}', 'DescriptionController@show')->middleware('auth');
// Route::post('/descriptions/{id}', 'DescriptionController@update')->middleware('auth');
Route::post('/descriptions', 'DescriptionController@store')->middleware('auth');
Route::delete('/descriptions/{id}', 'DescriptionController@delete')->middleware('auth');
Route::get('/descriptions/search/{value}/internship/{id}', 'DescriptionController@search')->middleware('auth');


Route::get('/requirements/categories', 'RequirementCategoryController@index')->middleware('auth');
Route::get('/requirements/categories/{id}', 'RequirementCategoryController@show')->middleware('auth');
Route::post('/requirements/categories/{id}', 'RequirementCategoryController@update')->middleware('auth');
Route::post('/requirements/categories', 'RequirementCategoryController@store')->middleware('auth');
Route::delete('/requirements/categories/{id}', 'RequirementCategoryController@delete')->middleware('auth');
Route::get('/requirements/categories/search/{value}', 'RequirementCategoryController@search')->middleware('auth');


Route::get('/requirements/internship/{id}', 'RequirementController@index')->middleware('auth');
Route::get('/requirements/search/{value}/internship/{id}', 'RequirementController@search')->middleware('auth');
Route::post('/requirements/{id}', 'RequirementController@update');


Route::get('/reports', 'ReportController@index')->middleware('auth');
Route::get('/reports/internship/{id}', 'ReportController@by_internship_id')->middleware('auth');
// Route::get('/reports/{id}', 'ReportController@show')->middleware('auth');
// Route::post('/reports/{id}', 'ReportController@update')->middleware('auth');
Route::post('/reports', 'ReportController@store')->middleware('auth');
Route::delete('/reports/{id}', 'ReportController@delete')->middleware('auth');
Route::get('/reports/search/{value}/internship/{id}', 'ReportController@search')->middleware('auth');

});