<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\WhatsappMessage;

class MessageController extends Controller
{
    // LISTAR MENSAGENS DE UM CHAMADO
    public function index(Ticket $ticket)
    {
        return $ticket->messages()->orderBy('criado_em')->get();
    }

    // ENVIAR RESPOSTA PELO WHATSAPP
    public function store(Request $request, Ticket $ticket)
    {
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
