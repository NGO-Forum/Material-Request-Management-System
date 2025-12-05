<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Roles\RoleController;
use App\Http\Controllers\V1\Departments\DepartmentController;
use App\Http\Controllers\V1\Users\UserController;
use App\Http\Controllers\V1\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



// PUBLIC ROUTES
Route::prefix('v1')->group(function () {

    // Register & Login
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // CRUD (requires token)
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('users', UserController::class)->middleware('auth:sanctum');
});

// PROTECTED ROUTES
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});


