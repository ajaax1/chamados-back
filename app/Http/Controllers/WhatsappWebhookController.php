<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\WhatsappMessage;

class WhatsappWebhookController extends Controller
{
    public function receive(Request $request)
    {
        $payload = $request->all();
        $numero = $payload['from'] ?? null;
        $texto  = $payload['text'] ?? '';

        if(!$numero || !$texto){
            return response()->json(['message'=>'Dados inválidos'],422);
        }

        // Tenta achar ticket aberto
        $ticket = Ticket::where('whatsapp_numero', $numero)
                        ->whereIn('status',['aberto','pendente'])
                        ->first();

        if(!$ticket){
            // IA tenta resolver
            $solucao = app('App\Services\IaService')->tentarResolver($texto);

            if($solucao['resolvido']){
                app('App\Services\WhatsappService')
                    ->sendMessage($numero, $solucao['resposta']);
                return response()->json(['message'=>'Resolvido pela IA']);
            }

            // Se não resolveu, cria chamado
            $ticket = Ticket::create([
                'nome_cliente' => 'Cliente WhatsApp',
                'whatsapp_numero' => $numero,
                'descricao' => $texto,
                'status' => 'pendente'
            ]);
        }

        // Registra mensagem recebida
        WhatsappMessage::create([
            'ticket_id' => $ticket->id,
            'mensagem' => $texto,
            'tipo' => 'recebido'
        ]);

        return response()->json(['message'=>'Mensagem processada']);
    }
}
