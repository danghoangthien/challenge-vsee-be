<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoungeController;
use App\Http\Controllers\WaitingRoomController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\SwaggerController;
use App\Http\Controllers\Api\EventsController;

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


// Route::get('/test-event', [TestController::class, 'sendTestEvent']);

// Swagger documentation routes
Route::group(['prefix' => 'docs'], function () {
    Route::get('/', [SwaggerController::class, 'api'])->name('l5-swagger.default.api');
    Route::get('docs.json', [SwaggerController::class, 'docs'])->name('l5-swagger.default.docs');
    Route::get('oauth2-callback', [SwaggerController::class, 'oauth2Callback'])->name('l5-swagger.default.oauth2-callback');
    Route::get('assets/{asset}', [SwaggerController::class, 'asset'])->name('l5-swagger.default.asset');
});

// Public routes (no auth required)
Route::post('/login', [AuthController::class, 'login']);

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
    Route::prefix('provider')->middleware(['auth:api', 'user', 'provider'])->group(function () {
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

// Test MongoDB Configuration Route
Route::get('/test-mongodb-config', function() {
    return response()->json([
        'config' => config('database.connections.mongodb'),
        'env' => [
            'MONGODB_URI' => env('MONGODB_URI'),
            'MONGODB_HOST' => env('MONGODB_HOST'),
            'MONGODB_PORT' => env('MONGODB_PORT'),
            'MONGODB_DATABASE' => env('MONGODB_DATABASE'),
            'MONGODB_USERNAME' => env('MONGODB_USERNAME'),
            'MONGODB_AUTH_DATABASE' => env('MONGODB_AUTH_DATABASE'),
        ]
    ]);
});

// Events documentation
Route::get('/events', [EventsController::class, 'index']);
