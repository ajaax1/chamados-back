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

    /**
     * Estatísticas de tendências (crescimento, etc)
     * GET /api/admin/statistics/trends
     */
    public function trends(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Acesso negado. Apenas administradores podem acessar.'], 403);
        }

        $days = $request->query('days', 30);
        $startDate = Carbon::now()->subDays($days);

        return response()->json([
            'days' => $days,
            'start_date' => $startDate,
            'tickets_trend' => $this->getTicketsTrend($startDate),
            'messages_trend' => $this->getMessagesTrend($startDate),
            'users_trend' => $this->getUsersTrend($startDate),
            'resolution_rate_trend' => $this->getResolutionRateTrend($startDate),
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

        // Prioridade: resolvido_em > tempo_resolucao > cálculo automático
        $times = $resolvedTickets->map(function ($ticket) {
            // 1. Se tem data/hora de resolução, usar ela
            if ($ticket->resolvido_em !== null) {
                return $ticket->resolvido_em->diffInHours($ticket->created_at);
            }
            // 2. Se tem tempo manual em minutos, usar ele
            if ($ticket->tempo_resolucao !== null) {
                return $ticket->tempo_resolucao / 60; // Converter minutos para horas
            }
            // 3. Calcular pela diferença de updated_at
            return $ticket->updated_at->diffInHours($ticket->created_at);
        });

        $manualTimes = $resolvedTickets->filter(function ($ticket) {
            return $ticket->tempo_resolucao !== null || $ticket->resolvido_em !== null;
        });

        $resolvidoEmCount = $resolvedTickets->filter(function ($ticket) {
            return $ticket->resolvido_em !== null;
        })->count();

        return [
            'average_hours' => round($times->avg(), 2),
            'average_days' => round($times->avg() / 24, 2),
            'average_minutes' => round($times->avg() * 60, 2),
            'min_hours' => $times->min(),
            'max_hours' => $times->max(),
            'using_manual_time' => $manualTimes->count() > 0,
            'manual_time_count' => $manualTimes->count(),
            'calculated_time_count' => $resolvedTickets->count() - $manualTimes->count(),
            'resolvido_em_count' => $resolvidoEmCount,
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
                // 1. Prioridade: resolvido_em
                if ($ticket->resolvido_em !== null) {
                    return $ticket->resolvido_em->diffInMinutes($ticket->created_at);
                }
                // 2. Tempo manual em minutos
                if ($ticket->tempo_resolucao !== null) {
                    return $ticket->tempo_resolucao; // Já está em minutos
                }
                // 3. Calcular automaticamente
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
            // 1. Prioridade: resolvido_em
            if ($ticket->resolvido_em !== null) {
                return $ticket->resolvido_em->diffInMinutes($ticket->created_at);
            }
            // 2. Tempo manual em minutos
            if ($ticket->tempo_resolucao !== null) {
                return $ticket->tempo_resolucao; // Já está em minutos
            }
            // 3. Calcular automaticamente
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

    private function getTicketsTrend($startDate)
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

    private function getMessagesTrend($startDate)
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

    private function getUsersTrend($startDate)
    {
        return User::where('created_at', '>=', $startDate)
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

    private function getResolutionRateTrend($startDate)
    {
        $tickets = Ticket::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw("sum(case when status in ('resolvido', 'finalizado') then 1 else 0 end) as resolved")
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $tickets->map(function ($item) {
            $rate = $item->total > 0 ? round(($item->resolved / $item->total) * 100, 2) : 0;
            return [
                'date' => $item->date,
                'total' => $item->total,
                'resolved' => $item->resolved,
                'rate' => $rate
            ];
        });
    }
}

