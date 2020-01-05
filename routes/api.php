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
Route::get('/companies/all', 'CompanyController@all');
Route::get('/companies/{id}', 'CompanyController@show');
Route::post('/companies/{id}', 'CompanyController@update');
Route::post('/companies', 'CompanyController@store');
Route::delete('/companies/{id}', 'CompanyController@delete');
Route::get('/companies/search/{value}', 'CompanyController@search');


Route::get('/internships', 'InternshipController@index');
Route::get('/internships/{id}', 'InternshipController@show');
Route::post('/internships/{id}', 'InternshipController@update');
Route::post('/internships', 'InternshipController@store');
Route::delete('/internships/{id}', 'InternshipController@delete');
Route::get('/internships/search/{value}', 'InternshipController@search');


Route::get('/descriptions', 'DescriptionController@index');
Route::get('/descriptions/internship/{id}', 'DescriptionController@by_internship_id');
// Route::get('/descriptions/{id}', 'DescriptionController@show');
// Route::post('/descriptions/{id}', 'DescriptionController@update');
Route::post('/descriptions', 'DescriptionController@store');
Route::delete('/descriptions/{id}', 'DescriptionController@delete');
Route::get('/descriptions/search/{value}/internship/{id}', 'DescriptionController@search');


Route::get('/requirements/categories', 'RequirementCategoryController@index');
Route::get('/requirements/categories/{id}', 'RequirementCategoryController@show');
Route::post('/requirements/categories/{id}', 'RequirementCategoryController@update');
Route::post('/requirements/categories', 'RequirementCategoryController@store');
Route::delete('/requirements/categories/{id}', 'RequirementCategoryController@delete');
Route::get('/requirements/categories/search/{value}', 'RequirementCategoryController@search');