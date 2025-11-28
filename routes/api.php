<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\WhatsappWebhookController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StatisticsController;

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
    Route::get('clientes', [UserController::class, 'getClientes']);

    // Ticket routes with role-based permissions
    Route::get('tickets-filtro', [TicketController::class, 'index']);
    Route::get('tickets-stats', [TicketController::class, 'stats']);
    Route::get('ticket/{id}', [TicketController::class, 'show']);

    Route::post('tickets', [TicketController::class, 'store']);

    Route::put('tickets/{ticket}', [TicketController::class, 'update']);

    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])
        ->middleware('role:support');

    // Messages - WhatsApp messages (mantido para compatibilidade)
    Route::get('tickets/{ticket}/messages', [MessageController::class, 'index']);
    Route::post('tickets/{ticket}/messages', [MessageController::class, 'store']);

    // Internal Messages - Sistema de mensagens internas do chamado
    Route::get('tickets/{ticket}/messages-internal', [MessageController::class, 'indexInternal']);
    Route::post('tickets/{ticket}/messages-internal', [MessageController::class, 'storeInternal']);

    // Message Attachments - Anexos de mensagens
    Route::get('message-attachments/{attachment}', [MessageController::class, 'showAttachment']);
    Route::get('message-attachments/{attachment}/download', [MessageController::class, 'downloadAttachment']);

    // Attachments - Upload, list, download and delete files
    Route::post('tickets/{ticket}/attachments', [AttachmentController::class, 'store']);
    Route::get('tickets/{ticket}/attachments', [AttachmentController::class, 'index']);
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show']);
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download']);
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);

    // Notifications - Gerenciar notificações do usuário
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'unread']);
    Route::get('notifications/count', [NotificationController::class, 'count']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);

    // Statistics - Estatísticas pessoais (qualquer usuário autenticado)
    Route::get('statistics/my-stats', [StatisticsController::class, 'myStats']);

    // Statistics - Estatísticas para administradores
    Route::prefix('admin/statistics')->middleware('role:admin')->group(function () {
        Route::get('dashboard', [StatisticsController::class, 'dashboard']);
        Route::get('tickets', [StatisticsController::class, 'tickets']);
        Route::get('users', [StatisticsController::class, 'users']);
        Route::get('messages', [StatisticsController::class, 'messages']);
        Route::get('attachments', [StatisticsController::class, 'attachments']);
    });

});
