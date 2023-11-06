<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great! ->middleware(['auth:sanctum'])
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/auth/login', [AuthController::class, 'loginUser']);

Route::get('/cars/index', [CarController::class, 'index']);
Route::post('/cars/store', [CarController::class, 'store'])->middleware(['auth:sanctum']);
Route::put('/cars/update/{id}', [CarController::class, 'updateShipping_status']);
