<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoungeController;
use App\Http\Controllers\WaitingRoomController;
use Illuminate\Support\Facades\DB;

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

// Public routes (no auth required)
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test-event', [TestController::class, 'sendTestEvent']);

// Protected routes (auth required)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Visitor-specific routes
    Route::prefix('visitor')->middleware(['auth:api', 'user', 'visitor'])->group(function () {
        Route::post('/lounge/queue', [LoungeController::class, 'enqueue']);
        Route::delete('/lounge/queue', [LoungeController::class, 'exit']);
    });

    // Provider-specific routes
    Route::prefix('provider')->middleware('provider')->group(function () {
        Route::get('/lounge/list', [LoungeController::class, 'getWaitingList']);
        Route::post('/lounge/pickup', [LoungeController::class, 'pickupVisitor']);
        Route::post('/lounge/dropoff', [LoungeController::class, 'dropoffVisitor']);
    });
});

// Test MongoDB Route
Route::get('/test-mongodb', function() {
    try {
        $collection = DB::connection('mongodb')->getCollection('test');
        $collection->insertOne(['name' => 'test', 'value' => 1]);
        return response()->json(['message' => 'MongoDB connection successful']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});