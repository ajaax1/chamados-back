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

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // Perfil do usuário logado
    Route::get('me', [UserController::class, 'me']);
    Route::put('me', [UserController::class, 'updateProfile']);

    // Usuários (somente admin)
    Route::apiResource('users', UserController::class)
        ->middleware('can:manage-users');

    // Chamados
    Route::apiResource('tickets', TicketController::class);
    Route::get('ticket/{id}', [TicketController::class, 'show']);
    // Mensagens de um chamado
    Route::get('tickets/{ticket}/messages', [MessageController::class, 'index']);
    Route::post('tickets/{ticket}/messages', [MessageController::class, 'store']);
});
