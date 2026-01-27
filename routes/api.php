<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Authentication Route
Route::prefix('auth')->group(function(){
    Route::get('/init', [
        'uses' => 'API\AuthController@init',
        'as' => 'auth.init'
    ])->middleware('auth:api');

    Route::post('/login', [
        'uses' => 'API\AuthController@login',
        'as' => 'auth.login'
    ]);

    Route::post('/register', [
        'uses' => 'API/AuthController@register',
        'as' => 'auth.register'
    ]);

    Route::get('/logout', [
        'uses' => 'API\AuthController@logout',
        'as' => 'auth.logout'
    ])->middleware('auth:api');
});

// User Routes
Route::group(['prefix' => 'user', 'middleware' => ['auth:api', 'token.type:authToken', 'user.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\UserController@index',
        'as' => 'user.index',
    ]);

    Route::get('/create', [
        'uses' => 'API\UserController@create',
        'as' => 'user.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\UserController@store',
        'as' => 'user.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\UserController@edit',
        'as' => 'user.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\UserController@update',
        'as' => 'user.update',
    ]);

    Route::post('/update_profile/{id}', [
        'uses' => 'API\UserController@update_profile',
        'as' => 'user.update_profile',
    ]);

    Route::post('/delete', [
        'uses' => 'API\UserController@delete',
        'as' => 'user.delete',
    ]);

    Route::get('/roles_permissions', [
        'uses' => 'API\UserController@userRolesPermissions',
        'as' => 'user.roles_permissions',
    ]);

    Route::post('/generate-qr-token', [
        'uses' => 'API\UserController@generateQrToken',
        'as' => 'user.generate.qr.token',
    ]);

    Route::post('/view-qr-token', [
        'uses' => 'API\UserController@viewQrToken',
        'as' => 'user.view.qr.token',
    ]);

    Route::get('/download-qr', [
        'uses' => 'API\UserController@download_qr',
        'as' => 'user.download.qr.code',
    ]);

});

//Permissions
Route::group(['prefix' => 'permission', 'middleware' => ['auth:api', 'token.type:authToken', 'permission.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\PermissionController@index',
        'as' => 'permission.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\PermissionController@create',
        'as' => 'permission.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\PermissionController@store',
        'as' => 'permission.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\PermissionController@edit',
        'as' => 'permission.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\PermissionController@update',
        'as' => 'permission.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\PermissionController@delete',
        'as' => 'permission.delete',
    ]);

});

//Roles
Route::group(['prefix' => 'role', 'middleware' => ['auth:api', 'token.type:authToken', 'role.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\RoleController@index',
        'as' => 'role.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\RoleController@create',
        'as' => 'role.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\RoleController@store',
        'as' => 'role.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\RoleController@edit',
        'as' => 'role.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\RoleController@update',
        'as' => 'role.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\RoleController@delete',
        'as' => 'role.delete',
    ]);

});

// Branch Routes
Route::group(['prefix' => 'branch', 'middleware' => ['auth:api', 'branch.maintenance']], function () {
  Route::get('/index', [
    'uses' => 'API\BranchController@index',
    'as' => 'branch.index',
  ]);
  Route::get('/create', [
    'uses' => 'API\BranchController@create',
    'as' => 'branch.create',
  ]);
  Route::post('/store', [
    'uses' => 'API\BranchController@store',
    'as' => 'branch.store',
  ]);
  Route::post('/edit', [
    'uses' => 'API\BranchController@edit',
    'as' => 'branch.edit',
  ]);
  Route::post('/update/{id}', [
    'uses' => 'API\BranchController@update',
    'as' => 'branch.update',
  ]);
  Route::post('/delete', [
    'uses' => 'API\BranchController@delete',
    'as' => 'branch.delete',
  ]);
});

// Company Routes
Route::group(['prefix' => 'company', 'middleware' => ['auth:api', 'company.maintenance']], function () {
    Route::get('/index', [
        'uses' => 'API\CompanyController@index',
        'as' => 'company.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\CompanyController@create',
        'as' => 'company.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\CompanyController@store',
        'as' => 'company.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\CompanyController@edit',
        'as' => 'company.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\CompanyController@update',
        'as' => 'company.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\CompanyController@delete',
        'as' => 'company.delete',
    ]);
});

// Position Routes
Route::group(['prefix' => 'position', 'middleware' => ['auth:api', 'position.maintenance']], function () {
    Route::get('/index', [
        'uses' => 'API\PositionController@index',
        'as' => 'position.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\PositionController@create',
        'as' => 'position.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\PositionController@store',
        'as' => 'position.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\PositionController@edit',
        'as' => 'position.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\PositionController@update',
        'as' => 'position.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\PositionController@delete',
        'as' => 'position.delete',
    ]);
});

// Department Routes
Route::group(['prefix' => 'department', 'middleware' => ['auth:api', 'department.maintenance']], function () {
    Route::get('/index', [
        'uses' => 'API\DepartmentController@index',
        'as' => 'department.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\DepartmentController@create',
        'as' => 'department.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\DepartmentController@store',
        'as' => 'department.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\DepartmentController@edit',
        'as' => 'department.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\DepartmentController@update',
        'as' => 'department.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\DepartmentController@delete',
        'as' => 'department.delete',
    ]);
});

// Division Routes
Route::group(['prefix' => 'division', 'middleware' => ['auth:api', 'division.maintenance']], function () {
    Route::get('/index', [
        'uses' => 'API\DivisionController@index',
        'as' => 'division.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\DivisionController@create',
        'as' => 'division.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\DivisionController@store',
        'as' => 'division.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\DivisionController@edit',
        'as' => 'division.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\DivisionController@update',
        'as' => 'division.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\DivisionController@delete',
        'as' => 'division.delete',
    ]);
});

// File Explorer Routes
Route::group(['prefix' => 'file-manager', 'middleware' => ['auth:api', 'file.manager.maintenance']], function () {
    Route::get('/index', [
        'uses' => 'API\FileManagerController@index',
        'as' => 'file.manager.index',
    ]);

    Route::post('/file-upload', [
        'uses' => 'API\FileManagerController@file_upload',
        'as' => 'file.manager.file.upload',
    ]);

    Route::post('/file-download/{id}', [
        'uses' => 'API\FileManagerController@file_download',
        'as' => 'file.manager.file.download',
    ]);

    Route::post('/file-delete', [
        'uses' => 'API\FileManagerController@file_delete',
        'as' => 'file.manager.file.delete',
    ]);

});

Route::post('/file-upload', [
    'uses' => 'API\FileManagerController@file_upload',
    'as' => 'file.upload',
]);


// validate token
Route::group(['prefix' => '/validate-token/{token}'], function(){
     Route::get('/index', [
        'uses' => 'API\FileManagerController@validateToken',
        'as' => 'file.manager.validateToken', 
    ]);
});

//Activity Logs
Route::group(['prefix' => 'activity_logs', 'middleware' => ['auth:api', 'activity.logs']], function(){
    Route::get('/index', [
        'uses' => 'API\ActivityLogController@activity_logs',
        'as' => 'activity_logs.index',
    ]);
    
});
