<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\WhatsappWebhookController;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);
Route::post('password/forgot', [AuthController::class, 'forgot']);

// Password Reset Routes
Route::post('password/forgot', [PasswordResetController::class, 'forgotPassword']);
Route::get('password/verify-token', [PasswordResetController::class, 'verifyToken']);
Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);

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

    // Change password (authenticated user)
    Route::post('password/change', [PasswordResetController::class, 'changePassword']);

    // User management - Admin can manage all users, others can only manage themselves
    Route::get('users', [UserController::class, 'index']);
    Route::get('users-stats', [UserController::class, 'stats']);
    Route::post('users', [UserController::class, 'store'])
        ->middleware('role:admin');
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('role:admin');

    Route::get('users-alphabetical', [UserController::class, 'getAllAlphabetical']);

    // Ticket routes with role-based permissions
    Route::get('tickets-filtro', [TicketController::class, 'index']);
    Route::get('tickets-stats', [TicketController::class, 'stats']);
    Route::get('ticket/{id}', [TicketController::class, 'show']);

    Route::post('tickets', [TicketController::class, 'store']);

    Route::put('tickets/{ticket}', [TicketController::class, 'update']);

    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])
        ->middleware('role:support');

    // Messages - All roles can view and create
    Route::get('tickets/{ticket}/messages', [MessageController::class, 'index']);
    Route::post('tickets/{ticket}/messages', [MessageController::class, 'store']);

});
