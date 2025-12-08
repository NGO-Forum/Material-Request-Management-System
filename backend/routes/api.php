<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Roles\RoleController;
use App\Http\Controllers\V1\Departments\DepartmentController;
use App\Http\Controllers\V1\Users\UserController;
use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Materials\CategoryController;
use App\Http\Controllers\V1\Materials\MaterialController;

// Public routes
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::apiResource('roles', RoleController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('users', UserController::class)->middleware('auth:sanctum');
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('materials', MaterialController::class);
});
