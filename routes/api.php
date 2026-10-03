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

/* feature-10032026-Maurice: unauthenticated POST-only, rate-limited provider boundary. */
Route::post('/academic/profile', 'AcademicProfileController@profile')->middleware('throttle:20,1');

Route::middleware(array('auth:api', 'role:student,coordinator,superuser'))->get('/user', 'UserController@current');
/* security-10032026-Maurice: route contract is auth:api role:student,coordinator,superuser (fail-closed). */
/* security-10032026-Maurice: Route::middleware('auth:api') is paired with the role boundary below. */
Route::middleware(array('auth:api', 'role:student,coordinator,superuser'))->group( function(){
    
Route::get('/users', 'UserController@index')->middleware('role:superuser');
Route::get('/users/{id}', 'UserController@show');
Route::post('/users/{id}', 'UserController@update')->middleware('role:superuser');
Route::post('/users', 'UserController@store')->middleware('role:superuser');
Route::delete('/users/{id}', 'UserController@delete')->middleware('role:superuser');
Route::get('/users/search/{value}', 'UserController@search')->middleware('role:superuser');
Route::get('/users/internship/requirement', 'UserController@internship_requirement')->middleware('role:superuser');

Route::get('/companies', 'CompanyController@index');
Route::get('/companies/all', 'CompanyController@all');
Route::get('/companies/{id}', 'CompanyController@show');
Route::post('/companies/{id}', 'CompanyController@update')->middleware('role:coordinator,superuser');
Route::post('/companies', 'CompanyController@store')->middleware('role:coordinator,superuser');
Route::delete('/companies/{id}', 'CompanyController@delete')->middleware('role:coordinator,superuser');
Route::get('/companies/search/{value}', 'CompanyController@search');
Route::get('/companies/cluster/{id}', 'CompanyController@cluster');
Route::get('/companies/cluster/status/{id}', 'CompanyController@cluster_status');
Route::get('/companies/status/{id}/{year}', 'CompanyController@company_status');


Route::get('/internships/schoolyears', 'InternshipController@schoolyears');
Route::get('/internships/campuses', 'InternshipController@campuses');
Route::get('/internships/semesters', 'InternshipController@semesters');
Route::get('/internships/colleges', 'InternshipController@colleges');
Route::get('/internships/courses/{college}', 'InternshipController@courses');
Route::get('/internships/majors/{course}', 'InternshipController@majors');
Route::get('/internships', 'InternshipController@index');
Route::get('/internships/{id}', 'InternshipController@show');
Route::post('/internships/{id}', 'InternshipController@update');
Route::post('/internships/{id}/approve', 'InternshipController@approve')->middleware('role:coordinator,superuser');
Route::post('/internships/clusters/companies/{id}', 'InternshipController@visit_company');
Route::post('/internships', 'InternshipController@store');
Route::delete('/internships/{id}', 'InternshipController@delete');
Route::get('/internships/search/{value}', 'InternshipController@search');



Route::get('/descriptions', 'DescriptionController@index');
Route::get('/descriptions/internship/{id}', 'DescriptionController@by_internship_id');
// Route::get('/descriptions/{id}', 'DescriptionController@show');
// Route::post('/descriptions/{id}', 'DescriptionController@update');
Route::post('/descriptions', 'DescriptionController@store');
Route::post('/descriptions/{id}', 'DescriptionController@update');
Route::delete('/descriptions/{id}', 'DescriptionController@delete');
Route::get('/descriptions/search/{value}/internship/{id}', 'DescriptionController@search');


Route::get('/requirements/categories', 'RequirementCategoryController@index');
Route::get('/requirements/categories/{id}', 'RequirementCategoryController@show');
Route::post('/requirements/categories/{id}', 'RequirementCategoryController@update')->middleware('role:coordinator,superuser');
Route::post('/requirements/categories', 'RequirementCategoryController@store')->middleware('role:coordinator,superuser');
Route::delete('/requirements/categories/{id}', 'RequirementCategoryController@delete')->middleware('role:coordinator,superuser');
Route::get('/requirements/categories/search/{value}', 'RequirementCategoryController@search');


Route::get('/requirements/internship/{id}', 'RequirementController@index');
Route::get('/requirements/search/{value}/internship/{id}', 'RequirementController@search');
 Route::post('/requirements/{id}', 'RequirementController@update')->middleware('role:coordinator,superuser');


Route::get('/reports', 'ReportController@index');
Route::get('/reports/internship/{id}', 'ReportController@by_internship_id');
// Route::get('/reports/{id}', 'ReportController@show');
Route::post('/reports/{id}', 'ReportController@update');
Route::post('/reports', 'ReportController@store');
Route::delete('/reports/{id}', 'ReportController@delete');
Route::get('/reports/search/{value}/internship/{id}', 'ReportController@search');


Route::get('/clusters', 'ClusterController@index');
Route::get('/clusters/all', 'ClusterController@all');
Route::get('/clusters/{id}', 'ClusterController@show');
Route::post('/clusters/{id}', 'ClusterController@update')->middleware('role:coordinator,superuser');
Route::post('/clusters', 'ClusterController@store')->middleware('role:coordinator,superuser');
Route::delete('/clusters/{id}', 'ClusterController@delete')->middleware('role:coordinator,superuser');
Route::get('/clusters/search/{value}', 'ClusterController@search');

});
