<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a password reset email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Mostrar configurações de email
        $this->info("🔧 Configurações de Email:");
        $this->info("Driver: " . config('mail.default'));
        $this->info("Host: " . config('mail.mailers.smtp.host'));
        $this->info("Port: " . config('mail.mailers.smtp.port'));
        $this->info("Encryption: " . config('mail.mailers.smtp.encryption'));
        $this->info("Username: " . config('mail.mailers.smtp.username'));
        $this->info("From: " . config('mail.from.address') . " (" . config('mail.from.name') . ")");
        $this->line('');

        // Verificar se o usuário existe
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário com email {$email} não encontrado.");
            return 1;
        }

        $this->info("📧 Enviando email de teste para: {$email}");

        try {
            // Simular dados de reset
            $resetLink = url("/reset-password?token=test-token&email=" . urlencode($email));

            \Log::info('Teste de email iniciado', [
                'email' => $email,
                'reset_link' => $resetLink,
                'mail_config' => config('mail')
            ]);

            Mail::send('emails.password-reset', [
                'resetLink' => $resetLink,
                'user' => $user
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Teste - Recuperação de Senha - Sistema de Chamados');
            });

            $this->info("✅ Email enviado com sucesso!");
            $this->info("📧 Verifique a caixa de entrada de: {$email}");
            $this->info("🔗 Link de teste: {$resetLink}");
            $this->info("📋 Verifique os logs em: storage/logs/laravel.log");

        } catch (\Exception $e) {
            $this->error("❌ Erro ao enviar email:");
            $this->error("Mensagem: " . $e->getMessage());
            $this->error("Arquivo: " . $e->getFile() . ":" . $e->getLine());
            $this->line('');
            $this->info("📋 Verifique os logs detalhados em: storage/logs/laravel.log");
            return 1;
        }

        return 0;
    }
}
