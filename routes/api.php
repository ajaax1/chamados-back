<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\WhatsappWebhookController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);
Route::post('password/forgot', [AuthController::class, 'forgot']);
Route::post('webhook/whatsapp', [WhatsappWebhookController::class, 'receive']);

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (precisam de token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('me', [UserController::class, 'me']);
    Route::put('me', [UserController::class, 'updateProfile']);

    Route::apiResource('users', UserController::class)
        ->middleware('can:manage-users');

    Route::get('users-alphabetical', [UserController::class, 'getAllAlphabetical']);
    Route::apiResource('tickets', TicketController::class);
    Route::get('ticket/{id}', [TicketController::class, 'show']);
    Route::get('tickets-filtro', [TicketController::class, 'index']);

    Route::get('tickets/{ticket}/messages', [MessageController::class, 'index']);
    Route::post('tickets/{ticket}/messages', [MessageController::class, 'store']);
});
