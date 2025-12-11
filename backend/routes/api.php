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

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Only roles & departments are public (you can lock later if needed)
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('departments', DepartmentController::class);
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Auth info
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // User Management
    Route::apiResource('users', UserController::class);

    // Materials
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('materials', MaterialController::class);

    // Material Request System (All CRUD)
    Route::apiResource('material-requests', MaterialRequestController::class);
    Route::patch('material-requests/{id}/status', [MaterialRequestController::class, 'updateStatus']);
    Route::post('material-requests/{id}/issue', [MaterialRequestController::class, 'issue']);
    Route::post('material-requests/{id}/return', [MaterialRequestController::class, 'returnMaterial']); 
    Route::apiResource('material-request-actions', MaterialRequestActionController::class);
    Route::apiResource('material-issue-records', MaterialIssueRecordController::class);
    Route::apiResource('material-returns', MaterialReturnController::class);
    Route::apiResource('material-stock-movements', MaterialStockMovementController::class);
});
