<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Roles\RoleController;
use App\Http\Controllers\V1\Departments\DepartmentController;
use App\Http\Controllers\V1\Users\UserController;

use App\Http\Controllers\V1\Materials\CategoryController;
use App\Http\Controllers\V1\Materials\MaterialController;

use App\Http\Controllers\V1\MaterialRequests\MaterialRequestController;
use App\Http\Controllers\V1\MaterialRequests\MaterialRequestActionController;
use App\Http\Controllers\V1\MaterialRequests\MaterialIssueRecordController;
use App\Http\Controllers\V1\MaterialRequests\MaterialReturnController;
use App\Http\Controllers\V1\MaterialRequests\MaterialStockMovementController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::apiResource('roles', RoleController::class);
    Route::apiResource('departments', DepartmentController::class);
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Users, Materials
    Route::apiResource('users', UserController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('materials', MaterialController::class);

    // Material Requests - Main CRUD + Issue/Return
    Route::apiResource('material-requests', MaterialRequestController::class);
    Route::patch('material-requests/{id}/status', [MaterialRequestController::class, 'updateStatus']);
    Route::post('material-requests/{id}/issue', [MaterialRequestController::class, 'issue']);
    Route::post('material-requests/{id}/return', [MaterialRequestController::class, 'returnMaterial']);

    // Supporting resources (read-only recommended)
    Route::get('material-request-actions', [MaterialRequestActionController::class, 'index']);
    Route::get('material-issue-records', [MaterialIssueRecordController::class, 'index']);
    Route::get('material-returns', [MaterialReturnController::class, 'index']);
    Route::get('material-stock-movements', [MaterialStockMovementController::class, 'index']);
});