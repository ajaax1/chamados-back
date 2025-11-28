<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketMessage;
use App\Models\TicketAttachment;
use App\Models\MessageAttachment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Dashboard geral - Visão geral do sistema
     * GET /api/admin/statistics/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Acesso negado. Apenas administradores podem acessar.'], 403);
        }

        $period = $request->query('period', 'month'); // day, week, month, year, all
        $startDate = $this->getStartDate($period);

        return response()->json([
            'period' => $period,
            'start_date' => $startDate,
            'tickets' => $this->getTicketsStats($startDate),
            'users' => $this->getUsersStats(),
            'messages' => $this->getMessagesStats($startDate),
            'performance' => $this->getPerformanceStats($startDate),
            'recent_activity' => $this->getRecentActivity(),
        ]);
    }

    /**
     * Estatísticas detalhadas de tickets
     * GET /api/admin/statistics/tickets
     */
    public function tickets(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Acesso negado. Apenas administradores podem acessar.'], 403);
        }

        $period = $request->query('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json([
            'period' => $period,
            'start_date' => $startDate,
            'overview' => $this->getTicketsStats($startDate),
            'by_status' => $this->getTicketsByStatus($startDate),
            'by_priority' => $this->getTicketsByPriority($startDate),
            'by_day' => $this->getTicketsByDay($startDate),
            'by_user' => $this->getTicketsByUser($startDate),
            'by_cliente' => $this->getTicketsByCliente($startDate),
            'resolution_time' => $this->getResolutionTimeStats($startDate),
            'resolution_time_by_cliente' => $this->getResolutionTimeByCliente($startDate),
            // Novas estatísticas
            'response_time' => $this->getResponseTimeStats($startDate),
            'agent_productivity' => $this->getAgentProductivity($startDate),
            'tickets_by_origin' => $this->getTicketsByOrigin($startDate),
            'tickets_created_by_period' => $this->getTicketsCreatedByPeriod($startDate, $period),
            'tickets_closed_by_period' => $this->getTicketsClosedByPeriod($startDate, $period),
            'tickets_by_agent_detailed' => $this->getTicketsByAgentDetailed($startDate),
        ]);
    }

    /**
     * Estatísticas de usuários e performance
     * GET /api/admin/statistics/users
     */
    public function users(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Acesso negado. Apenas administradores podem acessar.'], 403);
        }

        $period = $request->query('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json([
            'period' => $period,
            'start_date' => $startDate,
            'overview' => $this->getUsersStats(),
            'by_role' => $this->getUsersByRole(),
            'top_performers' => $this->getTopPerformers($startDate),
            'user_activity' => $this->getUserActivity($startDate),
            'average_resolution_time_by_cliente' => $this->getAverageResolutionTimeByCliente($startDate),
            'resolution_stats_by_user' => $this->getResolutionStatsByUser($startDate),
        ]);
    }

    /**
     * Estatísticas de mensagens
     * GET /api/admin/statistics/messages
     */
    public function messages(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Acesso negado. Apenas administradores podem acessar.'], 403);
        }

        $period = $request->query('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json([
            'period' => $period,
            'start_date' => $startDate,
            'overview' => $this->getMessagesStats($startDate),
            'by_day' => $this->getMessagesByDay($startDate),
            'by_user' => $this->getMessagesByUser($startDate),
            'internal_vs_external' => $this->getInternalVsExternalMessages($startDate),
        ]);
    }

    /**
     * Estatísticas de anexos
     * GET /api/admin/statistics/attachments
     */
    public function attachments(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Acesso negado. Apenas administradores podem acessar.'], 403);
        }

        $period = $request->query('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json([
            'period' => $period,
            'start_date' => $startDate,
            'overview' => $this->getAttachmentsStats($startDate),
            'by_type' => $this->getAttachmentsByType($startDate),
            'total_size' => $this->getTotalAttachmentsSize($startDate),
        ]);
    }

    // ========== MÉTODOS PRIVADOS ==========

    private function getStartDate($period)
    {
        switch ($period) {
            case 'day':
                return Carbon::today();
            case 'week':
                return Carbon::now()->startOfWeek();
            case 'month':
                return Carbon::now()->startOfMonth();
            case 'year':
                return Carbon::now()->startOfYear();
            case 'all':
            default:
                return Carbon::create(2020, 1, 1); // Data inicial do sistema
        }
    }

    private function getTicketsStats($startDate)
    {
        $query = Ticket::where('created_at', '>=', $startDate);

        return [
            'total' => $query->count(),
            'abertos' => (clone $query)->where('status', 'aberto')->count(),
            'pendentes' => (clone $query)->where('status', 'pendente')->count(),
            'resolvidos' => (clone $query)->where('status', 'resolvido')->count(),
            'finalizados' => (clone $query)->where('status', 'finalizado')->count(),
            'alta_prioridade' => (clone $query)->where('priority', 'alta')->count(),
            'media_prioridade' => (clone $query)->where('priority', 'média')->count(),
            'baixa_prioridade' => (clone $query)->where('priority', 'baixa')->count(),
        ];
    }

    private function getTicketsByStatus($startDate)
    {
        return Ticket::where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');
    }

    private function getTicketsByPriority($startDate)
    {
        return Ticket::where('created_at', '>=', $startDate)
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->get()
            ->pluck('total', 'priority');
    }

    private function getTicketsByDay($startDate)
    {
        return Ticket::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => $item->total
                ];
            });
    }

    private function getTicketsByUser($startDate)
    {
        return Ticket::where('tickets.created_at', '>=', $startDate)
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->id,
                    'user_name' => $item->name,
                    'total' => $item->total
                ];
            });
    }

    private function getTicketsByCliente($startDate)
    {
        return Ticket::where('tickets.created_at', '>=', $startDate)
            ->join('users', 'tickets.cliente_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'cliente_id' => $item->id,
                    'cliente_name' => $item->name,
                    'total' => $item->total
                ];
            });
    }

    private function getResolutionTimeStats($startDate)
    {
        $resolvedTickets = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return [
                'average_hours' => 0,
                'average_days' => 0,
                'average_minutes' => 0,
                'min_hours' => 0,
                'max_hours' => 0,
                'using_manual_time' => false,
            ];
        }

        // Prioridade: tempo_resolucao > cálculo automático
        $times = $resolvedTickets->map(function ($ticket) {
            // 1. Se tem tempo manual em minutos, usar ele
            if ($ticket->tempo_resolucao !== null) {
                return $ticket->tempo_resolucao / 60; // Converter minutos para horas
            }
            // 2. Calcular pela diferença de updated_at
            return $ticket->updated_at->diffInHours($ticket->created_at);
        });

        $manualTimes = $resolvedTickets->filter(function ($ticket) {
            return $ticket->tempo_resolucao !== null;
        });

        return [
            'average_hours' => round($times->avg(), 2),
            'average_days' => round($times->avg() / 24, 2),
            'average_minutes' => round($times->avg() * 60, 2),
            'min_hours' => $times->min(),
            'max_hours' => $times->max(),
            'using_manual_time' => $manualTimes->count() > 0,
            'manual_time_count' => $manualTimes->count(),
            'calculated_time_count' => $resolvedTickets->count() - $manualTimes->count(),
            'tempo_resolucao_count' => $resolvedTickets->filter(function ($ticket) {
                return $ticket->tempo_resolucao !== null;
            })->count(),
        ];
    }

    private function getResolutionTimeByCliente($startDate)
    {
        $resolvedTickets = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->whereNotNull('cliente_id')
            ->with('cliente')
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return [];
        }

        $byCliente = $resolvedTickets->groupBy('cliente_id')->map(function ($tickets, $clienteId) {
            $times = $tickets->map(function ($ticket) {
                // 1. Tempo manual em minutos
                if ($ticket->tempo_resolucao !== null) {
                    return $ticket->tempo_resolucao; // Já está em minutos
                }
                // 2. Calcular automaticamente
                return $ticket->updated_at->diffInMinutes($ticket->created_at);
            });

            $cliente = $tickets->first()->cliente;

            return [
                'cliente_id' => $clienteId,
                'cliente_name' => $cliente ? $cliente->name : 'N/A',
                'total_tickets' => $tickets->count(),
                'average_minutes' => round($times->avg(), 2),
                'average_hours' => round($times->avg() / 60, 2),
                'average_days' => round($times->avg() / (60 * 24), 2),
                'min_minutes' => $times->min(),
                'max_minutes' => $times->max(),
            ];
        })->values();

        return $byCliente->sortByDesc('total_tickets')->take(10)->values();
    }

    private function getAverageResolutionTimeByCliente($startDate)
    {
        $resolvedTickets = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->whereNotNull('cliente_id')
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return [
                'overall_average_minutes' => 0,
                'overall_average_hours' => 0,
                'overall_average_days' => 0,
                'total_resolved' => 0,
            ];
        }

        $times = $resolvedTickets->map(function ($ticket) {
            // 1. Tempo manual em minutos
            if ($ticket->tempo_resolucao !== null) {
                return $ticket->tempo_resolucao; // Já está em minutos
            }
            // 2. Calcular automaticamente
            return $ticket->updated_at->diffInMinutes($ticket->created_at);
        });

        $averageMinutes = $times->avg();

        return [
            'overall_average_minutes' => round($averageMinutes, 2),
            'overall_average_hours' => round($averageMinutes / 60, 2),
            'overall_average_days' => round($averageMinutes / (60 * 24), 2),
            'total_resolved' => $resolvedTickets->count(),
            'min_minutes' => $times->min(),
            'max_minutes' => $times->max(),
        ];
    }

    private function getUsersStats()
    {
        return [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'support' => User::where('role', 'support')->count(),
            'assistant' => User::where('role', 'assistant')->count(),
            'cliente' => User::where('role', 'cliente')->count(),
        ];
    }

    private function getUsersByRole()
    {
        return User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get()
            ->pluck('total', 'role');
    }

    private function getTopPerformers($startDate)
    {
        return Ticket::where('tickets.created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.role', DB::raw('count(*) as resolved'))
            ->groupBy('users.id', 'users.name', 'users.role')
            ->orderBy('resolved', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->id,
                    'user_name' => $item->name,
                    'role' => $item->role,
                    'resolved_tickets' => $item->resolved
                ];
            });
    }

    /**
     * Estatísticas de resolução por usuário
     * Retorna quantidade de chamados resolvidos e tempo médio por usuário
     */
    private function getResolutionStatsByUser($startDate)
    {
        $resolvedTickets = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->whereNotNull('user_id')
            ->with('user:id,name,email,role')
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return [];
        }

        // Agrupar por usuário
        $byUser = $resolvedTickets->groupBy('user_id')->map(function ($tickets, $userId) {
            $user = $tickets->first()->user;
            
            // Calcular tempos de resolução
            $times = $tickets->map(function ($ticket) {
                // 1. Se tem tempo manual em minutos, usar ele
                if ($ticket->tempo_resolucao !== null) {
                    return $ticket->tempo_resolucao; // Já está em minutos
                }
                // 2. Calcular automaticamente pela diferença de updated_at
                return $ticket->updated_at->diffInMinutes($ticket->created_at);
            });

            return [
                'user_id' => $userId,
                'user_name' => $user ? $user->name : 'N/A',
                'user_email' => $user ? $user->email : 'N/A',
                'user_role' => $user ? $user->role : 'N/A',
                'total_resolved' => $tickets->count(),
                'average_minutes' => round($times->avg(), 2),
                'average_hours' => round($times->avg() / 60, 2),
                'average_days' => round($times->avg() / (60 * 24), 2),
                'total_minutes' => round($times->sum(), 2),
                'total_hours' => round($times->sum() / 60, 2),
                'min_minutes' => $times->min(),
                'max_minutes' => $times->max(),
            ];
        })->values();

        // Ordenar por quantidade de chamados resolvidos (descendente)
        return $byUser->sortByDesc('total_resolved')->values()->all();
    }

    private function getUserActivity($startDate)
    {
        // Usuários que criaram tickets ou enviaram mensagens no período
        $ticketUsers = Ticket::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->pluck('user_id');

        $messageUsers = TicketMessage::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->pluck('user_id');

        $activeUsers = $ticketUsers->merge($messageUsers)->unique();

        return [
            'active_users' => $activeUsers->count(),
            'total_users' => User::count(),
            'activity_rate' => User::count() > 0 ? round(($activeUsers->count() / User::count()) * 100, 2) : 0,
        ];
    }

    private function getMessagesStats($startDate)
    {
        $query = TicketMessage::where('created_at', '>=', $startDate);

        return [
            'total' => $query->count(),
            'internal' => (clone $query)->where('is_internal', true)->count(),
            'external' => (clone $query)->where('is_internal', false)->count(),
        ];
    }

    private function getMessagesByDay($startDate)
    {
        return TicketMessage::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => $item->total
                ];
            });
    }

    private function getMessagesByUser($startDate)
    {
        return TicketMessage::where('ticket_messages.created_at', '>=', $startDate)
            ->join('users', 'ticket_messages.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->id,
                    'user_name' => $item->name,
                    'total' => $item->total
                ];
            });
    }

    private function getInternalVsExternalMessages($startDate)
    {
        return TicketMessage::where('created_at', '>=', $startDate)
            ->select('is_internal', DB::raw('count(*) as total'))
            ->groupBy('is_internal')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->is_internal ? 'internal' : 'external' => $item->total];
            });
    }

    private function getAttachmentsStats($startDate)
    {
        $ticketAttachments = TicketAttachment::where('created_at', '>=', $startDate)->count();
        $messageAttachments = MessageAttachment::where('created_at', '>=', $startDate)->count();

        return [
            'total' => $ticketAttachments + $messageAttachments,
            'ticket_attachments' => $ticketAttachments,
            'message_attachments' => $messageAttachments,
        ];
    }

    private function getAttachmentsByType($startDate)
    {
        $ticketAttachments = TicketAttachment::where('created_at', '>=', $startDate)
            ->select('tipo_mime', DB::raw('count(*) as total'))
            ->groupBy('tipo_mime')
            ->get();

        $messageAttachments = MessageAttachment::where('created_at', '>=', $startDate)
            ->select('tipo_mime', DB::raw('count(*) as total'))
            ->groupBy('tipo_mime')
            ->get();

        $combined = $ticketAttachments->concat($messageAttachments)
            ->groupBy('tipo_mime')
            ->map(function ($items) {
                return $items->sum('total');
            });

        return $combined;
    }

    private function getTotalAttachmentsSize($startDate)
    {
        $ticketSize = TicketAttachment::where('created_at', '>=', $startDate)->sum('tamanho');
        $messageSize = MessageAttachment::where('created_at', '>=', $startDate)->sum('tamanho');
        $total = $ticketSize + $messageSize;

        return [
            'bytes' => $total,
            'kb' => round($total / 1024, 2),
            'mb' => round($total / (1024 * 1024), 2),
            'gb' => round($total / (1024 * 1024 * 1024), 2),
        ];
    }

    private function getPerformanceStats($startDate)
    {
        $totalTickets = Ticket::where('created_at', '>=', $startDate)->count();
        $resolvedTickets = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->count();

        return [
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 2) : 0,
            'pending_tickets' => Ticket::where('created_at', '>=', $startDate)
                ->where('status', 'pendente')
                ->count(),
        ];
    }

    private function getRecentActivity()
    {
        return [
            'recent_tickets' => Ticket::orderBy('created_at', 'desc')
                ->limit(5)
                ->with('user', 'cliente')
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'title' => $ticket->title,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'user_name' => $ticket->user ? $ticket->user->name : null,
                        'cliente_name' => $ticket->cliente ? $ticket->cliente->name : null,
                        'created_at' => $ticket->created_at,
                    ];
                }),
            'recent_messages' => TicketMessage::orderBy('created_at', 'desc')
                ->limit(5)
                ->with('user', 'ticket')
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'ticket_id' => $message->ticket_id,
                        'ticket_title' => $message->ticket ? $message->ticket->title : null,
                        'user_name' => $message->user ? $message->user->name : null,
                        'is_internal' => $message->is_internal,
                        'created_at' => $message->created_at,
                    ];
                }),
        ];
    }

    /**
     * 🕒 Tempo de Resposta
     * Tempo médio até primeira resposta, solução e tempo total
     */
    private function getResponseTimeStats($startDate)
    {
        $tickets = Ticket::where('created_at', '>=', $startDate)
            ->with(['ticketMessages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->get();

        $firstResponseTimes = [];
        $resolutionTimes = [];
        $totalTimes = [];

        foreach ($tickets as $ticket) {
            // Tempo até primeira resposta (primeira mensagem do agente)
            $firstMessage = $ticket->ticketMessages()
                ->where('is_internal', false)
                ->whereHas('user', function ($q) {
                    $q->whereIn('role', ['admin', 'support', 'assistant']);
                })
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstMessage) {
                $firstResponseMinutes = $ticket->created_at->diffInMinutes($firstMessage->created_at);
                $firstResponseTimes[] = $firstResponseMinutes;
            }

            // Tempo até solução (quando foi resolvido)
            if (in_array($ticket->status, ['resolvido', 'finalizado'])) {
                if ($ticket->tempo_resolucao !== null) {
                    $resolutionTimes[] = $ticket->tempo_resolucao;
                } else {
                    $resolutionMinutes = $ticket->created_at->diffInMinutes($ticket->updated_at);
                    $resolutionTimes[] = $resolutionMinutes;
                }
            }

            // Tempo total do ticket aberto
            $totalMinutes = $ticket->created_at->diffInMinutes(now());
            $totalTimes[] = $totalMinutes;
        }

        return [
            'first_response' => [
                'average_minutes' => count($firstResponseTimes) > 0 ? round(array_sum($firstResponseTimes) / count($firstResponseTimes), 2) : 0,
                'average_hours' => count($firstResponseTimes) > 0 ? round((array_sum($firstResponseTimes) / count($firstResponseTimes)) / 60, 2) : 0,
                'tickets_with_response' => count($firstResponseTimes),
                'total_tickets' => $tickets->count(),
            ],
            'resolution_time' => [
                'average_minutes' => count($resolutionTimes) > 0 ? round(array_sum($resolutionTimes) / count($resolutionTimes), 2) : 0,
                'average_hours' => count($resolutionTimes) > 0 ? round((array_sum($resolutionTimes) / count($resolutionTimes)) / 60, 2) : 0,
                'resolved_tickets' => count($resolutionTimes),
            ],
            'total_open_time' => [
                'average_minutes' => count($totalTimes) > 0 ? round(array_sum($totalTimes) / count($totalTimes), 2) : 0,
                'average_hours' => count($totalTimes) > 0 ? round((array_sum($totalTimes) / count($totalTimes)) / 60, 2) : 0,
                'average_days' => count($totalTimes) > 0 ? round((array_sum($totalTimes) / count($totalTimes)) / (60 * 24), 2) : 0,
            ],
        ];
    }

    /**
     * 👨‍💻 Produtividade dos Agentes
     * Tickets atribuídos, fechados, tempo médio de resposta, taxa de resolução, não resolvidos
     */
    private function getAgentProductivity($startDate)
    {
        $tickets = Ticket::where('created_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->with('user:id,name,email,role')
            ->with(['ticketMessages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->get();

        $byUser = $tickets->groupBy('user_id')->map(function ($userTickets, $userId) {
            $user = $userTickets->first()->user;
            $assigned = $userTickets->count();
            $closed = $userTickets->whereIn('status', ['resolvido', 'finalizado'])->count();
            $notResolved = $userTickets->whereNotIn('status', ['resolvido', 'finalizado'])->count();
            $resolutionRate = $assigned > 0 ? round(($closed / $assigned) * 100, 2) : 0;

            // Calcular tempo médio de resposta
            $responseTimes = [];
            foreach ($userTickets as $ticket) {
                $firstMessage = $ticket->ticketMessages()
                    ->where('user_id', $userId)
                    ->where('is_internal', false)
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($firstMessage) {
                    $responseTimes[] = $ticket->created_at->diffInMinutes($firstMessage->created_at);
                }
            }

            // Calcular tempo médio de resolução
            $resolutionTimes = [];
            foreach ($userTickets->whereIn('status', ['resolvido', 'finalizado']) as $ticket) {
                if ($ticket->tempo_resolucao !== null) {
                    $resolutionTimes[] = $ticket->tempo_resolucao;
                } else {
                    $resolutionTimes[] = $ticket->created_at->diffInMinutes($ticket->updated_at);
                }
            }

            return [
                'user_id' => $userId,
                'user_name' => $user ? $user->name : 'N/A',
                'user_email' => $user ? $user->email : 'N/A',
                'user_role' => $user ? $user->role : 'N/A',
                'tickets_assigned' => $assigned,
                'tickets_closed' => $closed,
                'tickets_not_resolved' => $notResolved,
                'resolution_rate' => $resolutionRate,
                'average_response_time_minutes' => count($responseTimes) > 0 ? round(array_sum($responseTimes) / count($responseTimes), 2) : 0,
                'average_response_time_hours' => count($responseTimes) > 0 ? round((array_sum($responseTimes) / count($responseTimes)) / 60, 2) : 0,
                'average_resolution_time_minutes' => count($resolutionTimes) > 0 ? round(array_sum($resolutionTimes) / count($resolutionTimes), 2) : 0,
                'average_resolution_time_hours' => count($resolutionTimes) > 0 ? round((array_sum($resolutionTimes) / count($resolutionTimes)) / 60, 2) : 0,
            ];
        })->values();

        return $byUser->sortByDesc('tickets_assigned')->values()->all();
    }

    /**
     * 📥 Origens dos Tickets
     * Gráfico mostrando por onde chegaram os tickets
     */
    private function getTicketsByOrigin($startDate)
    {
        $tickets = Ticket::where('created_at', '>=', $startDate)
            ->select('origem', DB::raw('count(*) as total'))
            ->groupBy('origem')
            ->get();

        $total = $tickets->sum('total');
        $origins = [
            'formulario_web' => 0,
            'email' => 0,
            'api' => 0,
            'tel_manual' => 0,
            'null' => 0,
        ];

        foreach ($tickets as $ticket) {
            $key = $ticket->origem ?? 'null';
            $origins[$key] = $ticket->total;
        }

        return [
            'total' => $total,
            'by_origin' => [
                'formulario_web' => [
                    'total' => $origins['formulario_web'],
                    'percentage' => $total > 0 ? round(($origins['formulario_web'] / $total) * 100, 2) : 0,
                ],
                'email' => [
                    'total' => $origins['email'],
                    'percentage' => $total > 0 ? round(($origins['email'] / $total) * 100, 2) : 0,
                ],
                'api' => [
                    'total' => $origins['api'],
                    'percentage' => $total > 0 ? round(($origins['api'] / $total) * 100, 2) : 0,
                ],
                'tel_manual' => [
                    'total' => $origins['tel_manual'],
                    'percentage' => $total > 0 ? round(($origins['tel_manual'] / $total) * 100, 2) : 0,
                ],
                'null' => [
                    'total' => $origins['null'],
                    'percentage' => $total > 0 ? round(($origins['null'] / $total) * 100, 2) : 0,
                ],
            ],
        ];
    }

    /**
     * ✔️ Tickets criados por período
     * Por dia, semana ou mês - mostra picos de atendimento
     */
    private function getTicketsCreatedByPeriod($startDate, $period)
    {
        $groupBy = match($period) {
            'day' => DB::raw('DATE(created_at) as period'),
            'week' => DB::raw('YEARWEEK(created_at) as period'),
            'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
            default => DB::raw('DATE(created_at) as period'),
        };

        return Ticket::where('created_at', '>=', $startDate)
            ->select($groupBy, DB::raw('count(*) as total'))
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'total' => $item->total,
                ];
            });
    }

    /**
     * ✔️ Tickets fechados por período
     * Quantos foram resolvidos e comparação abertos x fechados
     */
    private function getTicketsClosedByPeriod($startDate, $period)
    {
        $groupByCreated = match($period) {
            'day' => DB::raw('DATE(created_at) as period'),
            'week' => DB::raw('YEARWEEK(created_at) as period'),
            'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
            default => DB::raw('DATE(created_at) as period'),
        };

        $groupByClosed = match($period) {
            'day' => DB::raw('DATE(updated_at) as period'),
            'week' => DB::raw('YEARWEEK(updated_at) as period'),
            'month' => DB::raw('DATE_FORMAT(updated_at, "%Y-%m") as period'),
            default => DB::raw('DATE(updated_at) as period'),
        };

        $closed = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->select($groupByClosed, DB::raw('count(*) as closed'))
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        $created = Ticket::where('created_at', '>=', $startDate)
            ->select($groupByCreated, DB::raw('count(*) as created'))
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        $periods = $created->keys()->merge($closed->keys())->unique()->sort();

        return $periods->map(function ($period) use ($created, $closed) {
            return [
                'period' => $period,
                'created' => $created->get($period)->created ?? 0,
                'closed' => $closed->get($period)->closed ?? 0,
                'open' => ($created->get($period)->created ?? 0) - ($closed->get($period)->closed ?? 0),
            ];
        })->values();
    }

    /**
     * ✔️ Tickets por agente (detalhado)
     * Quantos recebeu, respondeu e fechou
     */
    private function getTicketsByAgentDetailed($startDate)
    {
        $tickets = Ticket::where('created_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->with('user:id,name,email,role')
            ->with('ticketMessages')
            ->get();

        $byUser = $tickets->groupBy('user_id')->map(function ($userTickets, $userId) {
            $user = $userTickets->first()->user;
            $received = $userTickets->count();
            $responded = $userTickets->filter(function ($ticket) use ($userId) {
                return $ticket->ticketMessages()->where('user_id', $userId)->exists();
            })->count();
            $closed = $userTickets->whereIn('status', ['resolvido', 'finalizado'])->count();
            $notResolved = $userTickets->whereNotIn('status', ['resolvido', 'finalizado'])->count();

            return [
                'user_id' => $userId,
                'user_name' => $user ? $user->name : 'N/A',
                'user_email' => $user ? $user->email : 'N/A',
                'user_role' => $user ? $user->role : 'N/A',
                'tickets_received' => $received,
                'tickets_responded' => $responded,
                'tickets_closed' => $closed,
                'tickets_not_resolved' => $notResolved,
                'response_rate' => $received > 0 ? round(($responded / $received) * 100, 2) : 0,
                'resolution_rate' => $received > 0 ? round(($closed / $received) * 100, 2) : 0,
            ];
        })->values();

        return $byUser->sortByDesc('tickets_received')->values()->all();
    }

    /**
     * Estatísticas pessoais do usuário logado
     * GET /api/statistics/my-stats
     */
    public function myStats(Request $request)
    {
        $user = $request->user();
        $period = $request->query('period', 'month');
        $startDate = $this->getStartDate($period);

        return response()->json([
            'period' => $period,
            'start_date' => $startDate,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'overview' => $this->getMyTicketsStats($user->id, $startDate),
            'by_status' => $this->getMyTicketsByStatus($user->id, $startDate),
            'by_priority' => $this->getMyTicketsByPriority($user->id, $startDate),
            'by_day' => $this->getMyTicketsByDay($user->id, $startDate),
            'response_time' => $this->getMyResponseTimeStats($user->id, $startDate),
            'productivity' => $this->getMyProductivity($user->id, $startDate),
            'tickets_by_origin' => $this->getMyTicketsByOrigin($user->id, $startDate),
            'tickets_created_by_period' => $this->getMyTicketsCreatedByPeriod($user->id, $startDate, $period),
            'tickets_closed_by_period' => $this->getMyTicketsClosedByPeriod($user->id, $startDate, $period),
        ]);
    }

    // ========== MÉTODOS PRIVADOS PARA ESTATÍSTICAS PESSOAIS ==========

    private function getMyTicketsStats($userId, $startDate)
    {
        $query = Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate);

        return [
            'total' => $query->count(),
            'abertos' => (clone $query)->where('status', 'aberto')->count(),
            'pendentes' => (clone $query)->where('status', 'pendente')->count(),
            'resolvidos' => (clone $query)->where('status', 'resolvido')->count(),
            'finalizados' => (clone $query)->where('status', 'finalizado')->count(),
            'alta_prioridade' => (clone $query)->where('priority', 'alta')->count(),
            'media_prioridade' => (clone $query)->where('priority', 'média')->count(),
            'baixa_prioridade' => (clone $query)->where('priority', 'baixa')->count(),
        ];
    }

    private function getMyTicketsByStatus($userId, $startDate)
    {
        return Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');
    }

    private function getMyTicketsByPriority($userId, $startDate)
    {
        return Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->get()
            ->pluck('total', 'priority');
    }

    private function getMyTicketsByDay($userId, $startDate)
    {
        return Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total' => $item->total
                ];
            });
    }

    private function getMyResponseTimeStats($userId, $startDate)
    {
        $tickets = Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->with(['ticketMessages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->get();

        $firstResponseTimes = [];
        $resolutionTimes = [];
        $totalTimes = [];

        foreach ($tickets as $ticket) {
            // Tempo até primeira resposta (primeira mensagem do agente)
            $firstMessage = $ticket->ticketMessages()
                ->where('is_internal', false)
                ->where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstMessage) {
                $firstResponseMinutes = $ticket->created_at->diffInMinutes($firstMessage->created_at);
                $firstResponseTimes[] = $firstResponseMinutes;
            }

            // Tempo até solução (quando foi resolvido)
            if (in_array($ticket->status, ['resolvido', 'finalizado'])) {
                if ($ticket->tempo_resolucao !== null) {
                    $resolutionTimes[] = $ticket->tempo_resolucao;
                } else {
                    $resolutionMinutes = $ticket->created_at->diffInMinutes($ticket->updated_at);
                    $resolutionTimes[] = $resolutionMinutes;
                }
            }

            // Tempo total do ticket aberto
            $totalMinutes = $ticket->created_at->diffInMinutes(now());
            $totalTimes[] = $totalMinutes;
        }

        return [
            'first_response' => [
                'average_minutes' => count($firstResponseTimes) > 0 ? round(array_sum($firstResponseTimes) / count($firstResponseTimes), 2) : 0,
                'average_hours' => count($firstResponseTimes) > 0 ? round((array_sum($firstResponseTimes) / count($firstResponseTimes)) / 60, 2) : 0,
                'tickets_with_response' => count($firstResponseTimes),
                'total_tickets' => $tickets->count(),
            ],
            'resolution_time' => [
                'average_minutes' => count($resolutionTimes) > 0 ? round(array_sum($resolutionTimes) / count($resolutionTimes), 2) : 0,
                'average_hours' => count($resolutionTimes) > 0 ? round((array_sum($resolutionTimes) / count($resolutionTimes)) / 60, 2) : 0,
                'resolved_tickets' => count($resolutionTimes),
            ],
            'total_open_time' => [
                'average_minutes' => count($totalTimes) > 0 ? round(array_sum($totalTimes) / count($totalTimes), 2) : 0,
                'average_hours' => count($totalTimes) > 0 ? round((array_sum($totalTimes) / count($totalTimes)) / 60, 2) : 0,
                'average_days' => count($totalTimes) > 0 ? round((array_sum($totalTimes) / count($totalTimes)) / (60 * 24), 2) : 0,
            ],
        ];
    }

    private function getMyProductivity($userId, $startDate)
    {
        $tickets = Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->with(['ticketMessages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->get();

        $assigned = $tickets->count();
        $closed = $tickets->whereIn('status', ['resolvido', 'finalizado'])->count();
        $notResolved = $tickets->whereNotIn('status', ['resolvido', 'finalizado'])->count();
        $resolutionRate = $assigned > 0 ? round(($closed / $assigned) * 100, 2) : 0;

        // Calcular tempo médio de resposta
        $responseTimes = [];
        foreach ($tickets as $ticket) {
            $firstMessage = $ticket->ticketMessages()
                ->where('user_id', $userId)
                ->where('is_internal', false)
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstMessage) {
                $responseTimes[] = $ticket->created_at->diffInMinutes($firstMessage->created_at);
            }
        }

        // Calcular tempo médio de resolução
        $resolutionTimes = [];
        foreach ($tickets->whereIn('status', ['resolvido', 'finalizado']) as $ticket) {
            if ($ticket->tempo_resolucao !== null) {
                $resolutionTimes[] = $ticket->tempo_resolucao;
            } else {
                $resolutionTimes[] = $ticket->created_at->diffInMinutes($ticket->updated_at);
            }
        }

        // Tickets respondidos
        $responded = $tickets->filter(function ($ticket) use ($userId) {
            return $ticket->ticketMessages()->where('user_id', $userId)->exists();
        })->count();

        return [
            'tickets_assigned' => $assigned,
            'tickets_closed' => $closed,
            'tickets_not_resolved' => $notResolved,
            'tickets_responded' => $responded,
            'resolution_rate' => $resolutionRate,
            'response_rate' => $assigned > 0 ? round(($responded / $assigned) * 100, 2) : 0,
            'average_response_time_minutes' => count($responseTimes) > 0 ? round(array_sum($responseTimes) / count($responseTimes), 2) : 0,
            'average_response_time_hours' => count($responseTimes) > 0 ? round((array_sum($responseTimes) / count($responseTimes)) / 60, 2) : 0,
            'average_resolution_time_minutes' => count($resolutionTimes) > 0 ? round(array_sum($resolutionTimes) / count($resolutionTimes), 2) : 0,
            'average_resolution_time_hours' => count($resolutionTimes) > 0 ? round((array_sum($resolutionTimes) / count($resolutionTimes)) / 60, 2) : 0,
        ];
    }

    private function getMyTicketsByOrigin($userId, $startDate)
    {
        $tickets = Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select('origem', DB::raw('count(*) as total'))
            ->groupBy('origem')
            ->get();

        $total = $tickets->sum('total');
        $origins = [
            'formulario_web' => 0,
            'email' => 0,
            'api' => 0,
            'tel_manual' => 0,
            'null' => 0,
        ];

        foreach ($tickets as $ticket) {
            $key = $ticket->origem ?? 'null';
            $origins[$key] = $ticket->total;
        }

        return [
            'total' => $total,
            'by_origin' => [
                'formulario_web' => [
                    'total' => $origins['formulario_web'],
                    'percentage' => $total > 0 ? round(($origins['formulario_web'] / $total) * 100, 2) : 0,
                ],
                'email' => [
                    'total' => $origins['email'],
                    'percentage' => $total > 0 ? round(($origins['email'] / $total) * 100, 2) : 0,
                ],
                'api' => [
                    'total' => $origins['api'],
                    'percentage' => $total > 0 ? round(($origins['api'] / $total) * 100, 2) : 0,
                ],
                'tel_manual' => [
                    'total' => $origins['tel_manual'],
                    'percentage' => $total > 0 ? round(($origins['tel_manual'] / $total) * 100, 2) : 0,
                ],
                'null' => [
                    'total' => $origins['null'],
                    'percentage' => $total > 0 ? round(($origins['null'] / $total) * 100, 2) : 0,
                ],
            ],
        ];
    }

    private function getMyTicketsCreatedByPeriod($userId, $startDate, $period)
    {
        $groupBy = match($period) {
            'day' => DB::raw('DATE(created_at) as period'),
            'week' => DB::raw('YEARWEEK(created_at) as period'),
            'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
            default => DB::raw('DATE(created_at) as period'),
        };

        return Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select($groupBy, DB::raw('count(*) as total'))
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'total' => $item->total,
                ];
            });
    }

    private function getMyTicketsClosedByPeriod($userId, $startDate, $period)
    {
        $groupByCreated = match($period) {
            'day' => DB::raw('DATE(created_at) as period'),
            'week' => DB::raw('YEARWEEK(created_at) as period'),
            'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
            default => DB::raw('DATE(created_at) as period'),
        };

        $groupByClosed = match($period) {
            'day' => DB::raw('DATE(updated_at) as period'),
            'week' => DB::raw('YEARWEEK(updated_at) as period'),
            'month' => DB::raw('DATE_FORMAT(updated_at, "%Y-%m") as period'),
            default => DB::raw('DATE(updated_at) as period'),
        };

        $closed = Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolvido', 'finalizado'])
            ->select($groupByClosed, DB::raw('count(*) as closed'))
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        $created = Ticket::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select($groupByCreated, DB::raw('count(*) as created'))
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get()
            ->keyBy('period');

        $periods = $created->keys()->merge($closed->keys())->unique()->sort();

        return $periods->map(function ($period) use ($created, $closed) {
            return [
                'period' => $period,
                'created' => $created->get($period)->created ?? 0,
                'closed' => $closed->get($period)->closed ?? 0,
                'open' => ($created->get($period)->created ?? 0) - ($closed->get($period)->closed ?? 0),
            ];
        })->values();
    }
}

