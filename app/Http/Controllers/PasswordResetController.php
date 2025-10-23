<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /**
     * Solicitar reset de senha
     */
    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.exists' => 'Este email não está cadastrado em nosso sistema.'
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Email não encontrado'], 404);
        }

        // Gerar token único
        $token = Str::random(64);

        // Remover tokens antigos para este email
        PasswordReset::where('email', $data['email'])->delete();

        // Criar novo token
        PasswordReset::create([
            'email' => $data['email'],
            'token' => $token,
            'created_at' => now()
        ]);

        // Criar link de reset para o frontend
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $resetLink = $frontendUrl . "/reset-password?token={$token}&email=" . urlencode($data['email']);

        // Enviar email
        try {
            \Log::info('Tentando enviar email de recuperação', [
                'email' => $user->email,
                'reset_link' => $resetLink,
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'username' => config('mail.mailers.smtp.username'),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            ]);

            Mail::send('emails.password-reset', [
                'resetLink' => $resetLink,
                'user' => $user
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Recuperação de Senha - Sistema de Chamados');
            });

            \Log::info('Email enviado com sucesso', [
                'email' => $user->email,
                'reset_link' => $resetLink
            ]);

            return response()->json([
                'message' => 'Link de recuperação enviado para seu email.',
                'expires_in' => '1 hora',
                'debug' => [
                    'email_sent_to' => $user->email,
                    'reset_link' => $resetLink
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de recuperação', [
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'username' => config('mail.mailers.smtp.username')
                ]
            ]);

            // Em caso de erro no email, retornar o link (apenas para desenvolvimento)
            return response()->json([
                'message' => 'Link de recuperação gerado.',
                'reset_link' => $resetLink, // Apenas para desenvolvimento
                'token' => $token, // Apenas para desenvolvimento
                'expires_in' => '1 hora',
                'note' => 'Configure o email em produção',
                'error' => $e->getMessage(),
                'debug_info' => [
                    'email_sent_to' => $user->email,
                    'reset_link' => $resetLink,
                    'mail_config' => config('mail')
                ]
            ]);
        }
    }

    /**
     * Verificar se o token é válido (GET ou POST)
     */
    public function verifyToken(Request $request)
    {
        // Aceitar tanto query parameters (GET) quanto body (POST)
        $token = $request->query('token') ?? $request->input('token');
        $email = $request->query('email') ?? $request->input('email');

        \Log::info('Verificando token de reset', [
            'token' => $token,
            'email' => $email,
            'query_params' => $request->query(),
            'input_data' => $request->input()
        ]);

        if (!$token || !$email) {
            \Log::warning('Token ou email não fornecidos', [
                'token_provided' => !empty($token),
                'email_provided' => !empty($email)
            ]);
            return response()->json(['message' => 'Token e email são obrigatórios'], 400);
        }

        $passwordReset = PasswordReset::where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$passwordReset) {
            \Log::warning('Token não encontrado no banco', [
                'email' => $email,
                'token' => $token
            ]);
            return response()->json(['message' => 'Token inválido ou expirado'], 400);
        }

        \Log::info('Token encontrado no banco', [
            'email' => $email,
            'created_at' => $passwordReset->created_at,
            'created_at_type' => gettype($passwordReset->created_at)
        ]);

        // Verificar se o token não expirou (1 hora)
        $createdAt = \Carbon\Carbon::parse($passwordReset->created_at);
        $hoursDiff = now()->diffInHours($createdAt);

        \Log::info('Verificando expiração do token', [
            'created_at' => $createdAt->toISOString(),
            'now' => now()->toISOString(),
            'hours_diff' => $hoursDiff
        ]);

        if ($hoursDiff > 1) {
            \Log::info('Token expirado, removendo do banco', [
                'email' => $email,
                'hours_diff' => $hoursDiff
            ]);
            $passwordReset->delete();
            return response()->json(['message' => 'Token expirado'], 400);
        }

        $expiresAt = $createdAt->addHour()->toISOString();

        \Log::info('Token válido', [
            'email' => $email,
            'expires_at' => $expiresAt
        ]);

        return response()->json([
            'message' => 'Token válido',
            'email' => $email,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Resetar a senha
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed'
        ], [
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.'
        ]);

        // Verificar se o token é válido
        $passwordReset = PasswordReset::where('email', $data['email'])
            ->where('token', $data['token'])
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Token inválido'], 400);
        }

        // Verificar se o token não expirou
        if (now()->diffInHours($passwordReset->created_at) > 1) {
            $passwordReset->delete();
            return response()->json(['message' => 'Token expirado'], 400);
        }

        // Atualizar a senha do usuário
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        // Remover o token usado
        $passwordReset->delete();

        return response()->json([
            'message' => 'Senha alterada com sucesso!'
        ]);
    }

    /**
     * Alterar senha (usuário logado)
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ], [
            'current_password.required' => 'A senha atual é obrigatória.',
            'password.required' => 'A nova senha é obrigatória.',
            'password.min' => 'A nova senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.'
        ]);

        // Verificar senha atual
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Senha atual incorreta'], 400);
        }

        // Atualizar senha
        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        return response()->json([
            'message' => 'Senha alterada com sucesso!'
        ]);
    }
}
