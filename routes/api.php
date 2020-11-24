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

Route::get('/public', function (Request $request) {
    return "Public";
});

Route::get('/private', function (Request $request) {
    return "Private";
})->middleware('auth:api');


// Get All Roles
Route::get('/v1/roles', 'RoleController@get')->middleware('auth:api');

// Employee
Route::get('/v1/employeeinfo', 'EmployeeController@getEmployeeInfo')->middleware('auth:api');
Route::post('/v1/employees', 'EmployeeController@create')->middleware('auth:api');
Route::get('/v1/employees', 'EmployeeController@get')->middleware('auth:api');
Route::get('/v1/employees/{id}', 'EmployeeController@getOneById')->middleware('auth:api');
Route::put('/v1/employees/{id}', 'EmployeeController@update')->middleware('auth:api');
Route::delete('/v1/employees/{id}', 'EmployeeController@delete')->middleware('auth:api');

// Intake form
Route::post('/v1/intakes', 'IntakeController@create')->middleware('auth:api');
Route::put('/v1/intakes/{id}', 'IntakeController@update')->middleware('auth:api');
Route::put('/v1/intakes/{id}/approve', 'IntakeController@approve')->middleware('auth:api');
Route::get('/v1/intakes', 'IntakeController@get')->middleware('auth:api');
Route::get('/v1/intakes/{id}', 'IntakeController@getOneById')->middleware('auth:api');
Route::delete('/v1/intakes/{id}', 'IntakeController@delete')->middleware('auth:api');

// Order
Route::get('/v1/orders', 'OrderController@get')->middleware('auth:api');
Route::get('/v1/orders/{id}', 'OrderController@getOneById')->middleware('auth:api');
Route::put('/v1/orders/{id}', 'OrderController@update')->middleware('auth:api');

// Service
Route::get('/v1/services', 'ServiceController@get')->middleware('auth:api');
Route::post('/v1/services', 'ServiceController@create')->middleware('auth:api');
Route::put('/v1/services/{id}', 'ServiceController@update')->middleware('auth:api');
Route::delete('/v1/services/{id}', 'ServiceController@delete')->middleware('auth:api');

// Variant
Route::get('/v1/variants', 'VariantController@get')->middleware('auth:api');
Route::get('/v1/variants/{id}', 'VariantController@getOneById')->middleware('auth:api');
Route::post('/v1/variants', 'VariantController@create')->middleware('auth:api');
Route::put('/v1/variants/{id}', 'VariantController@update')->middleware('auth:api');
Route::delete('/v1/variants/{id}', 'VariantController@delete')->middleware('auth:api');

// Service Category
Route::get('/v1/service-categories', 'ServiceCategoryController@get')->middleware('auth:api');
Route::post('/v1/service-categories', 'ServiceCategoryController@create')->middleware('auth:api');


// Customer
Route::post('/v1/customers', 'CustomerController@create')->middleware('auth:api');
Route::get('/v1/customers', 'CustomerController@get')->middleware('auth:api');
Route::get('/v1/customers/{id}', 'CustomerController@getOneById')->middleware('auth:api');
Route::put('/v1/customers/{id}', 'CustomerController@update')->middleware('auth:api');


// Combo
Route::post('/v1/combos', 'ComboController@create')->middleware('auth:api');
Route::get('/v1/combos', 'ComboController@get')->middleware('auth:api');
Route::get('/v1/combos/{id}', 'ComboController@getOneById')->middleware('auth:api');
// Route::put('/v1/combos/{id}', 'ComboController@update')->middleware('auth:api');
// Route::delete('v1/combos/{id}', 'ComboController@delete')->middleware('auth:api');

// Package
Route::post('/v1/packages', 'PackageController@create')->middleware('auth:api');
Route::get('/v1/packages', 'PackageController@get')->middleware('auth:api');
Route::get('/v1/packages/{id}', 'PackageController@getOneById')->middleware('auth:api');
Route::put('/v1/packages/{id}', 'PackageController@update')->middleware('auth:api');
Route::delete('v1/packages/{id}', 'PackageController@delete')->middleware('auth:api');

// ReviewForm
Route::post('/v1/reviews', 'ReviewFormController@create')->middleware('auth:api');

// Statistic
Route::get('/v1/statistics', 'StatisticController@get')->middleware('auth:api');


// Account
Route::put('/v1/change-password', 'UserController@updatePassword')->middleware('auth:api');

// Invoice
Route::post('/v1/invoice', 'InvoiceController@create')->middleware('auth:api');
Route::put('/v1/invoice/approve/{id}', 'InvoiceController@approve')->middleware('auth:api');
Route::get('/v1/invoice/{customerId}', 'InvoiceController@getInvoiceByCustomerId')->middleware('auth:api');