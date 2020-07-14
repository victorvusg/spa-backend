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

// Get User Info
Route::get('/v1/userinfo', 'UserController@getUserInfo')->middleware('auth:api');
// Create User
Route::post('/v1/users', 'UserController@create')->middleware('auth:api');
// Get All User
Route::get('/v1/users', 'UserController@get')->middleware('auth:api');
// Get One User By Id
Route::get('/v1/users/{id}', 'UserController@getOneById')->middleware('auth:api');
// Update One User By Id
Route::put('/v1/users/{id}', 'UserController@update')->middleware('auth:api');
// Delete One User By Id
Route::delete('/v1/users/{id}', 'UserController@delete')->middleware('auth:api');

// Get all services
Route::get('/v1/services', 'ServiceController@get')->middleware('auth:api');

// Create service
Route::post('/v1/services', 'ServiceController@create')->middleware('auth:api');

// Customer
Route::post('/v1/customers', 'CustomerController@create')->middleware('auth:api');
Route::get('/v1/customers', 'CustomerController@get')->middleware('auth:api');
