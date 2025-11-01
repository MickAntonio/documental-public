<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserInterinoController;

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

Route::group(['middleware' => 'auth'], function() {

    Route::get('/admin/users', [UserController::class, 'index']);
    Route::post('/admin/users', [UserController::class, 'store'])->middleware('role:manage-users');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->middleware('role:manage-users');
    Route::get('/admin/users/{id}', [UserController::class, 'show'])->middleware('role:manage-users');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->middleware('role:manage-users');

    Route::get('/admin/groups', [GroupController::class, 'index']);
    Route::get('/admin/groups/user/{id}', [GroupController::class, 'userGroups']);
    Route::get('/admin/roles', [RoleController::class, 'index']);
    Route::get('/admin/roles/user/{id}', [RoleController::class, 'userRoles']);

    

});

Route::get('/admin/users-interinos', [UserInterinoController::class, 'index']);
Route::post('/admin/users-interino', [UserInterinoController::class, 'store']);
Route::delete('/admin/users-interino/{id}', [UserInterinoController::class, 'destroy']);

Route::get('/admin/users-interinos-teste', [UserInterinoController::class, 'teste']);


Route::get('/audits',[AuditController::class,'index']);
Route::get('/audits/{id}',[AuditController::class,'show']);
Route::delete('/audits/{id}',[AuditController::class,'destroy']);
Route::post('/audits/periodo',[AuditController::class,'destroyByPeriod']);

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index']);