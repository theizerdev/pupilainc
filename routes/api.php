<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;

use App\Http\Controllers\Api\JwtAuthController;

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

// Authentication Routes (JWT)
Route::post('/login', [JwtAuthController::class, 'login']);
Route::post('/auth/login', [JwtAuthController::class, 'login']);
Route::post('/auth/verify-token', [JwtAuthController::class, 'verifyToken']);
Route::get('/pacientes/sintomas/get', [JwtAuthController::class, 'getSymptoms']);
Route::get('/pacientes/{doctor_id?}', [JwtAuthController::class, 'getNotesOrPatients']);
Route::post('/pacientes', [JwtAuthController::class, 'addNote']);
Route::get('/doctores', [JwtAuthController::class, 'getDoctors']);
Route::get('/doctores/{id}', [JwtAuthController::class, 'getDoctor']);
Route::post('/doctores', [JwtAuthController::class, 'addDoctor']);
Route::post('/citas', [JwtAuthController::class, 'addAppointment']);

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [JwtAuthController::class, 'logout']);
    Route::post('/auth/refresh', [JwtAuthController::class, 'refresh']);
    Route::get('/auth/me', [JwtAuthController::class, 'me']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// WhatsApp API Routes
Route::prefix('whatsapp')->group(function () {
    Route::get('/status', [WhatsAppController::class, 'status']);
    Route::get('/qr-code', [WhatsAppController::class, 'qrCode']);
    Route::post('/send-message', [WhatsAppController::class, 'sendMessage']);
    Route::get('/messages', [WhatsAppController::class, 'messages']);
    Route::post('/connect', [WhatsAppController::class, 'connect']);
    Route::post('/disconnect', [WhatsAppController::class, 'disconnect']);
    Route::post('/webhook', [WhatsAppController::class, 'webhook']);
});