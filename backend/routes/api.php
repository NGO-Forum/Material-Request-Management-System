<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Roles\RoleController;
use App\Http\Controllers\V1\Departments\DepartmentController;
use App\Http\Controllers\V1\Users\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Group routes under /api/v1 prefix
Route::prefix('v1')->group(function () {
    // Roles CRUD
    Route::apiResource('roles', RoleController::class);
    // Departments CRUD
    Route::apiResource('departments', DepartmentController::class);
    // Users CRUD
    Route::apiResource('users', UserController::class);
});


