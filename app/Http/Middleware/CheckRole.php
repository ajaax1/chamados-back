<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user has the required role
        switch ($role) {
            case 'admin':
                if (!$user->isAdmin()) {
                    return response()->json(['message' => 'Acesso negado. Função de administrador requerida.'], 403);
                }
                break;
            case 'support':
                if (!$user->isSupport() && !$user->isAdmin()) {
                    return response()->json(['message' => 'Acesso negado. Função de suporte ou administrador requerida.'], 403);
                }
                break;
            case 'assistant':
                // Assistant is the default role, so any authenticated user can access
                break;
            default:
                return response()->json(['message' => 'Função inválida especificada'], 400);
        }

        return $next($request);
    }
}
