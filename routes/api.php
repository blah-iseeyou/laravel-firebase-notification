<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FcmTokenController;

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


Route::post('/fcm-register-token', [FcmTokenController::class, 'store']);
Route::get('/send-notifications', [FcmTokenController::class, 'sendNotifications']);
Route::get('/validate-tokens', [FcmTokenController::class, 'validateTokens']);
Route::get('/multiple-notifications', [FcmTokenController::class, 'sendMultipleNotificationsTokens']);
Route::get('/add-topic', [FcmTokenController::class, 'addTokenToTopic']);
Route::get('/send-topic', [FcmTokenController::class, 'sendToTopic']);
Route::get('/unremove-token-from-topic', [FcmTokenController::class, 'removeTokenFromTopic']);
Route::get('/notificaciones-condicion', [FcmTokenController::class, 'sendNotificationsBasedOnConditions']);
// Route::get('/token', [FcmTokenController::class, 'getToken']);