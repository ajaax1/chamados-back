<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketController extends Controller
{
    // LISTAR COM FILTROS
    public function index(Request $request)
    {
        $query = Ticket::query();

        if ($request->filled('q')) $query->search($request->q);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        }

        return $query->with('user')->paginate(15);
    }

    // CRIAR CHAMADO MANUAL
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'title' => 'required|string|max:250',
                'nome_cliente' => 'required|string|max:100',
                'whatsapp_numero' => 'nullable|string|max:20',
                'descricao' => 'required|string',
                'status' => 'required|nullable|in:aberto,pendente,resolvido,finalizado',
            ],
            [
                'nome_cliente.required' => 'O nome do cliente é obrigatório.',
                'descricao.required' => 'A descrição do chamado é obrigatória.',
                'status.in' => 'O status deve ser um dos seguintes: aberto, pendente, resolvido, finalizado.',
                'whatsapp_numero.max' => 'O número de WhatsApp não pode exceder 20 caracteres.',
                'status.required' => 'O status é obrigatório.',
            ]
        );

        $data['user_id'] = $request->user()->id;
        $ticket = Ticket::create($data);
        return response()->json($ticket, 201);
    }

    // VISUALIZAR
    public function show(Ticket $ticket)
    {
        return $ticket->load('user', 'messages');
    }

    // ATUALIZAR
    public function update(Request $request, int $id)
    {
        $data = $request->validate(
            [
                'nome_cliente' => 'sometimes|string|max:100',
                'whatsapp_numero' => 'nullable|string|max:20',
                'descricao' => 'sometimes|string',
                'status' => 'sometimes|nullable|in:aberto,pendente,resolvido,finalizado',
            ],
            [
                'status.in' => 'O status deve ser um dos seguintes: aberto, pendente, resolvido, finalizado.',
                'whatsapp_numero.max' => 'O número de WhatsApp não pode exceder 20 caracteres.',
            ]
        );
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Chamado não encontrado'], 404);
        }

        $ticket->update($data);

        // Recarrega o ticket atualizado do banco
        $updatedTicket = Ticket::find($id);

        return response()->json($updatedTicket, 200);
    }

    // DELETAR
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Chamado excluído']);
    }
}
