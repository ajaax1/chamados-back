<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Listar logs de atividades
     * GET /api/activity-logs
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ActivityLog::with('user:id,name,email,role');

        // Filtros
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->has('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        if ($request->has('period')) {
            $startDate = $this->getStartDate($request->period);
            $query->where('created_at', '>=', $startDate);
        } elseif ($request->has('from') && $request->has('to')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            ]);
        }

        // Cliente só vê seus próprios logs
        if ($user->isCliente()) {
            $query->where('user_id', $user->id);
        }

        // Ordenar por mais recente primeiro
        $query->orderBy('created_at', 'desc');

        // Paginação
        $perPage = min((int) $request->get('per_page', 50), 100);
        
        return $query->paginate($perPage);
    }

    /**
     * Visualizar um log específico
     * GET /api/activity-logs/{id}
     */
    public function show($id)
    {
        $log = ActivityLog::with('user:id,name,email,role')->find($id);

        if (!$log) {
            return response()->json(['message' => 'Log não encontrado'], 404);
        }

        return response()->json($log);
    }

    /**
     * Logs de um usuário específico
     * GET /api/activity-logs/user/{userId}
     */
    public function userLogs(Request $request, $userId)
    {
        $currentUser = $request->user();

        // Cliente só pode ver seus próprios logs
        if ($currentUser->isCliente() && $currentUser->id != $userId) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $query = ActivityLog::where('user_id', $userId)
            ->with('user:id,name,email,role')
            ->orderBy('created_at', 'desc');

        if ($request->has('period')) {
            $startDate = $this->getStartDate($request->period);
            $query->where('created_at', '>=', $startDate);
        }

        $perPage = min((int) $request->get('per_page', 50), 100);
        
        return $query->paginate($perPage);
    }

    /**
     * Logs de um ticket específico
     * GET /api/activity-logs/ticket/{ticketId}
     */
    public function ticketLogs(Request $request, $ticketId)
    {
        $user = $request->user();
        
        // Verificar se o usuário tem acesso ao ticket
        $ticket = \App\Models\Ticket::find($ticketId);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket não encontrado'], 404);
        }

        if (!$user->canViewTicket($ticket)) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $query = ActivityLog::where('model_type', \App\Models\Ticket::class)
            ->where('model_id', $ticketId)
            ->with('user:id,name,email,role')
            ->orderBy('created_at', 'desc');

        $perPage = min((int) $request->get('per_page', 50), 100);
        
        return $query->paginate($perPage);
    }

    /**
     * Estatísticas dos logs
     * GET /api/activity-logs/stats
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $query = ActivityLog::query();

        // Cliente só vê seus próprios logs
        if ($user->isCliente()) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('period')) {
            $startDate = $this->getStartDate($request->period);
            $query->where('created_at', '>=', $startDate);
        }

        $total = $query->count();
        $byAction = $query->clone()
            ->selectRaw('action, count(*) as total')
            ->groupBy('action')
            ->get()
            ->pluck('total', 'action');

        $byModelType = $query->clone()
            ->selectRaw('model_type, count(*) as total')
            ->groupBy('model_type')
            ->get()
            ->pluck('total', 'model_type');

        return response()->json([
            'total' => $total,
            'by_action' => $byAction,
            'by_model_type' => $byModelType,
        ]);
    }

    /**
     * Calcula data inicial baseada no período
     */
    private function getStartDate($period)
    {
        return match($period) {
            'day' => Carbon::today()->startOfDay(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            'all' => Carbon::create(2000, 1, 1),
            default => Carbon::now()->startOfMonth(),
        };
    }
}
