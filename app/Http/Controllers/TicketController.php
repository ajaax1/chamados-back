<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->query('from'), $request->query('to')]);
        }

        $query->orderBy('created_at', 'desc');

        return $query->with('user')->paginate(10);
    }


    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'title' => 'required|string|max:250',
                'nome_cliente' => 'required|string|max:100',
                'whatsapp_numero' => 'nullable|string|max:20',
                'descricao' => 'required|string',
                'status' => 'required|nullable|in:aberto,pendente,resolvido,finalizado',
                'priority' => 'required|in:baixa,média,alta',
            ],
            [
                'nome_cliente.required' => 'O nome do cliente é obrigatório.',
                'descricao.required' => 'A descrição do chamado é obrigatória.',
                'status.in' => 'O status deve ser um dos seguintes: aberto, pendente, resolvido, finalizado.',
                'whatsapp_numero.max' => 'O número de WhatsApp não pode exceder 20 caracteres.',
                'status.required' => 'O status é obrigatório.',
                'title.required' => 'O título do chamado é obrigatório.',
                'priority.in' => 'A prioridade deve ser um dos seguintes: baixa, média, alta.',
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

    public function update(Request $request, int $id)
    {
        $data = $request->validate(
            [
                'title' => 'string|max:250',
                'nome_cliente' => 'string|max:100',
                'whatsapp_numero' => 'nullable|string|max:20',
                'descricao' => 'string',
                'status' => 'nullable|in:aberto,pendente,resolvido,finalizado',
                'priority' => 'in:baixa,média,alta',
            ],
            [
                'status.in' => 'O status deve ser um dos seguintes: aberto, pendente, resolvido, finalizado.',
                'whatsapp_numero.max' => 'O número de WhatsApp não pode exceder 20 caracteres.',
                'priority.in' => 'A prioridade deve ser um dos seguintes: baixa, média, alta.',
            ]
        );
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Chamado não encontrado'], 404);
        }

        $ticket->fill($data);
        $ticket->save();

        return response()->json($ticket->fresh(), 200);
    }


    // DELETAR
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(['message' => 'Chamado excluído']);
    }

    // ESTATÍSTICAS DOS TICKETS
    public function stats()
    {
        $total = Ticket::count();
        $abertos = Ticket::where('status', 'aberto')->count();
        $resolvidos = Ticket::where('status', 'resolvido')->count();
        $pendentes = Ticket::where('status', 'pendente')->count();

        return response()->json([
            'total' => $total,
            'abertos' => $abertos,
            'resolvidos' => $resolvidos,
            'pendentes' => $pendentes
        ]);
    }
}
