<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Listar todas as notificações do usuário autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()->paginate(20);
        
        return response()->json($notifications);
    }

    /**
     * Listar apenas notificações não lidas
     */
    public function unread(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->unreadNotifications()->paginate(20);
        
        return response()->json($notifications);
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['message' => 'Notificação não encontrada'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json(['message' => 'Notificação marcada como lida']);
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['message' => 'Todas as notificações foram marcadas como lidas']);
    }

    /**
     * Contar notificações não lidas
     */
    public function count(Request $request)
    {
        $user = $request->user();
        
        $count = $user->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Deletar uma notificação
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['message' => 'Notificação não encontrada'], 404);
        }
        
        $notification->delete();
        
        return response()->json(['message' => 'Notificação deletada']);
    }
}
