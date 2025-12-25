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

    // Roles & Departments can be public if needed
    Route::apiResource('roles', RoleController::class)->only(['index', 'show']);
    Route::apiResource('departments', DepartmentController::class)->only(['index', 'show']);
});

// Protected routes (Auth required)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Users (admin only)
    Route::apiResource('users', UserController::class);

    // Materials & Categories
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('materials', MaterialController::class);

    // Material Requests CRUD + Actions
    Route::apiResource('material-requests', MaterialRequestController::class);
    Route::patch('material-requests/{id}/status', [MaterialRequestController::class, 'updateStatus']);
    Route::post('material-requests/{id}/issue', [MaterialRequestController::class, 'issue']);
    Route::post('material-requests/{id}/initiate-return', [MaterialRequestController::class, 'initiateReturn']);
    Route::post('material-requests/{id}/inspect-return', [MaterialRequestController::class, 'inspectReturn']);
    Route::post('material-requests/{id}/confirm-return', [MaterialRequestController::class, 'confirmReturn']);

    // Supporting read-only resources
    Route::get('material-request-actions', [MaterialRequestActionController::class, 'index']);
    Route::get('material-issue-records', [MaterialIssueRecordController::class, 'index']);
    Route::get('material-returns', [MaterialReturnController::class, 'index']);
    Route::get('material-stock-movements', [MaterialStockMovementController::class, 'index']);
});
