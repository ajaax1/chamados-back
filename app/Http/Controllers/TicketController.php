<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::query();

        // Cliente só pode ver seus próprios tickets
        if ($user->isCliente()) {
            $query->where('cliente_id', $user->id);
        } elseif (!$user->canViewAllTickets()) {
            // Assistant só vê tickets atribuídos a ele
            $query->where('user_id', $user->id);
        }

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($clienteId = $request->query('cliente_id')) {
            $query->where('cliente_id', $clienteId);
        }

        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->query('from'), $request->query('to')]);
        }

        $query->orderBy('created_at', 'desc');

        return $query->with('user', 'cliente', 'attachments')->paginate(10);
    }


    public function store(Request $request)
    {
        $user = $request->user();

        $validationRules = [
            'title' => 'required|string|max:250',
            'nome_cliente' => 'required|string|max:100',
            'whatsapp_numero' => 'nullable|string|max:20',
            'descricao' => 'required|string',
            'status' => 'required|nullable|in:aberto,pendente,resolvido,finalizado',
            'priority' => 'required|in:baixa,média,alta',
            'tempo_resolucao' => 'nullable|integer|min:0',
            'prazo_resolucao' => 'nullable|date',
            'origem' => 'nullable|in:formulario_web,email,api,tel_manual',
        ];

        // Admin e support podem definir cliente_id e user_id
        if ($user->canViewAllTickets()) {
            $validationRules['cliente_id'] = 'nullable|exists:users,id';
            $validationRules['user_id'] = 'nullable|exists:users,id';
        }

        $data = $request->validate(
            $validationRules,
            [
                'nome_cliente.required' => 'O nome do cliente é obrigatório.',
                'descricao.required' => 'A descrição do chamado é obrigatória.',
                'status.in' => 'O status deve ser um dos seguintes: aberto, pendente, resolvido, finalizado.',
                'whatsapp_numero.max' => 'O número de WhatsApp não pode exceder 20 caracteres.',
                'status.required' => 'O status é obrigatório.',
                'title.required' => 'O título do chamado é obrigatório.',
                'priority.in' => 'A prioridade deve ser um dos seguintes: baixa, média, alta.',
                'cliente_id.exists' => 'O cliente não existe.',
                'user_id.exists' => 'O usuário não existe.',
                'tempo_resolucao.integer' => 'O tempo de resolução deve ser um número inteiro.',
                'tempo_resolucao.min' => 'O tempo de resolução não pode ser negativo.',
                'prazo_resolucao.date' => 'O prazo de resolução deve ser uma data válida.',
                'origem.in' => 'A origem deve ser uma das seguintes: formulario_web, email, api, tel_manual.',
            ]
        );

        // Se for cliente, automaticamente define cliente_id como o próprio usuário
        if ($user->isCliente()) {
            $data['cliente_id'] = $user->id;
        }

        // Se não foi definido user_id e não é cliente, define como o usuário logado
        if (!isset($data['user_id']) && !$user->isCliente()) {
            $data['user_id'] = $user->id;
        }

        $ticket = Ticket::create($data);

        // Enviar notificações quando um ticket é criado
        $this->sendTicketNotifications($ticket, null);

        return response()->json($ticket->load('user', 'cliente', 'attachments'), 201);
    }

    // VISUALIZAR
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Chamado não encontrado'], 404);
        }

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para ver este chamado.'], 403);
        }

        return response()->json($ticket->load('user', 'cliente', 'messages', 'attachments'));
    }

    public function update(Request $request, int $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Chamado não encontrado'], 404);
        }

        // Verificar permissão para editar o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para editar este chamado.'], 403);
        }

        // Cliente não pode editar tickets (apenas visualizar)
        if ($user->isCliente()) {
            return response()->json(['message' => 'Acesso negado. Clientes não podem editar chamados.'], 403);
        }

        $validationRules = [
            'title' => 'string|max:250',
            'nome_cliente' => 'string|max:100',
            'whatsapp_numero' => 'nullable|string|max:20',
            'descricao' => 'string',
            'status' => 'nullable|in:aberto,pendente,resolvido,finalizado',
            'priority' => 'in:baixa,média,alta',
            'tempo_resolucao' => 'nullable|integer|min:0',
            'prazo_resolucao' => 'nullable|date',
            'origem' => 'nullable|in:formulario_web,email,api,tel_manual',
        ];

        // Admin e support podem alterar cliente_id e user_id
        if ($user->canViewAllTickets()) {
            $validationRules['cliente_id'] = 'nullable|exists:users,id';
            $validationRules['user_id'] = 'nullable|exists:users,id';
        }

        $data = $request->validate(
            $validationRules,
            [
                'status.in' => 'O status deve ser um dos seguintes: aberto, pendente, resolvido, finalizado.',
                'whatsapp_numero.max' => 'O número de WhatsApp não pode exceder 20 caracteres.',
                'priority.in' => 'A prioridade deve ser um dos seguintes: baixa, média, alta.',
                'cliente_id.exists' => 'O cliente não existe.',
                'user_id.exists' => 'O usuário não existe.',
                'tempo_resolucao.integer' => 'O tempo de resolução deve ser um número inteiro.',
                'tempo_resolucao.min' => 'O tempo de resolução não pode ser negativo.',
                'prazo_resolucao.date' => 'O prazo de resolução deve ser uma data válida.',
                'origem.in' => 'A origem deve ser uma das seguintes: formulario_web, email, api, tel_manual.',
            ]
        );

        // Verificar se user_id ou cliente_id foi alterado
        $oldUserId = $ticket->user_id;
        $oldClienteId = $ticket->cliente_id;
        $oldStatus = $ticket->status;
        $oldTempoResolucao = $ticket->tempo_resolucao;

        $ticket->fill($data);
        
        // Calcular tempo de resolução automaticamente quando status muda para resolvido/finalizado
        $newStatus = isset($data['status']) ? $data['status'] : $ticket->status;
        $isResolving = in_array($newStatus, ['resolvido', 'finalizado']) && 
                       !in_array($oldStatus, ['resolvido', 'finalizado']);
        
        // Se está resolvendo e não foi fornecido tempo_resolucao manualmente e não tinha antes
        if ($isResolving && !isset($data['tempo_resolucao']) && $oldTempoResolucao === null) {
            // Calcular tempo em minutos entre criação e agora
            $tempoEmMinutos = $ticket->created_at->diffInMinutes(now());
            $ticket->tempo_resolucao = $tempoEmMinutos;
        }
        
        $ticket->save();

        // Enviar notificações se user_id ou cliente_id foi alterado
        if ($oldUserId !== $ticket->user_id || $oldClienteId !== $ticket->cliente_id) {
            $this->sendTicketNotifications($ticket, [
                'old_user_id' => $oldUserId,
                'old_cliente_id' => $oldClienteId
            ]);
        }

        return response()->json($ticket->fresh()->load('user', 'cliente', 'attachments'), 200);
    }


    // DELETAR
    public function destroy(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Verificar permissão para ver o ticket
        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado. Você não tem permissão para ver este chamado.'], 403);
        }

        // Cliente não pode deletar tickets
        if ($user->isCliente()) {
            return response()->json(['message' => 'Acesso negado. Clientes não podem deletar chamados.'], 403);
        }

        // Check if user can delete this ticket
        if (!$user->canDeleteTickets() && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Acesso negado. Você só pode deletar seus próprios tickets.'], 403);
        }

        $ticket->delete();
        return response()->json(['message' => 'Chamado excluído']);
    }

    // ESTATÍSTICAS DOS TICKETS
    public function stats(Request $request)
    {
        $user = $request->user();
        $query = Ticket::query();

        // Cliente só vê estatísticas dos seus próprios tickets
        if ($user->isCliente()) {
            $query->where('cliente_id', $user->id);
        } elseif (!$user->canViewAllTickets()) {
            // Assistant só vê estatísticas dos tickets atribuídos a ele
            $query->where('user_id', $user->id);
        }

        $total = $query->count();
        $abertos = (clone $query)->where('status', 'aberto')->count();
        $resolvidos = (clone $query)->where('status', 'resolvido')->count();
        $pendentes = (clone $query)->where('status', 'pendente')->count();

        return response()->json([
            'total' => $total,
            'abertos' => $abertos,
            'resolvidos' => $resolvidos,
            'pendentes' => $pendentes
        ]);
    }

    /**
     * Envia notificações quando um ticket é atribuído a um usuário
     */
    private function sendTicketNotifications(Ticket $ticket, $oldData = null)
    {
        // Notificar o usuário atribuído (user_id) - admin, support ou assistant
        if ($ticket->user_id) {
            $assignedUser = User::find($ticket->user_id);
            if ($assignedUser) {
                // Só notificar se for uma nova atribuição ou se mudou
                if (!$oldData || $oldData['old_user_id'] !== $ticket->user_id) {
                    $assignedUser->notify(new TicketAssignedNotification($ticket, 'user'));
                }
            }
        }

        // Notificar o cliente (cliente_id)
        if ($ticket->cliente_id) {
            $cliente = User::find($ticket->cliente_id);
            if ($cliente) {
                // Só notificar se for uma nova atribuição ou se mudou
                if (!$oldData || $oldData['old_cliente_id'] !== $ticket->cliente_id) {
                    $cliente->notify(new TicketAssignedNotification($ticket, 'cliente'));
                }
            }
        }

        // Notificar todos os admins quando um novo ticket é criado
        if (!$oldData) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                // Não notificar o admin se ele já foi notificado como user_id
                if ($ticket->user_id !== $admin->id) {
                    $admin->notify(new TicketAssignedNotification($ticket, 'user'));
                }
            }
        }
    }
}
