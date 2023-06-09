<?php

use App\Models\Statusflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\StatusflowController;

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

Route::get('/get-next-status/{current_status}', [StatusflowController::class, 'getNextStatus']);
Route::post('/check-is-allowed', [StatusflowController::class, 'checkIsAllowedChangeStatus']);

// * Delivery
Route::apiResource('/deliveries', DeliveryController::class);
Route::get('/deliveries/get-next-status/{id}', [DeliveryController::class, 'getNextStatus']);
Route::post('/deliveries/update-status', [DeliveryController::class, 'updateStatus']);