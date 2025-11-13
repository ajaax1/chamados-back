<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\WhatsappMessage;

class MessageController extends Controller
{
    // LISTAR MENSAGENS DE UM CHAMADO
    public function index(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para ver este chamado.'], 403);
        }

        return $ticket->messages()->orderBy('criado_em')->get();
    }

    // ENVIAR RESPOSTA PELO WHATSAPP
    public function store(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para enviar mensagens neste chamado.'], 403);
        }

        $data = $request->validate([
            'mensagem' => 'required|string'
        ]);

        $msg = $ticket->messages()->create([
            'mensagem' => $data['mensagem'],
            'tipo' => 'enviado'
        ]);

        // Enfileira envio real via WhatsAppService
        dispatch(function() use ($ticket, $msg){
            app('App\Services\WhatsappService')
                ->sendMessage($ticket->whatsapp_numero, $msg->mensagem);
        });

        return $msg;
    }
}
