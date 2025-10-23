<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestTokenVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:token-verification {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test token verification process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Verificar se o usuário existe
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuário com email {$email} não encontrado.");
            return 1;
        }

        $this->info("🧪 Testando Verificação de Token para: {$email}");
        $this->line('');

        // Criar um token de teste
        $token = Str::random(64);

        // Remover tokens antigos
        PasswordReset::where('email', $email)->delete();

        // Criar novo token
        $passwordReset = PasswordReset::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now()
        ]);

        $this->info("✅ Token criado: {$token}");
        $this->info("📅 Criado em: " . $passwordReset->created_at);
        $this->info("🔍 Tipo de created_at: " . gettype($passwordReset->created_at));
        $this->line('');

        // Testar verificação de token via API
        $this->info("🌐 Testando API de verificação...");

        try {
            $response = \Http::get("http://localhost:8000/api/password/verify-token", [
                'token' => $token,
                'email' => $email
            ]);

            $this->info("📡 Status da resposta: " . $response->status());
            $this->info("📄 Resposta: " . $response->body());

            if ($response->successful()) {
                $this->info("✅ Token verificado com sucesso!");
            } else {
                $this->error("❌ Erro na verificação do token");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro ao testar API:");
            $this->error($e->getMessage());
        }

        $this->line('');

        // Testar manualmente a lógica
        $this->info("🔧 Testando lógica manual...");

        try {
            $createdAt = Carbon::parse($passwordReset->created_at);
            $hoursDiff = now()->diffInHours($createdAt);
            $expiresAt = $createdAt->addHour()->toISOString();

            $this->info("📅 Created at (Carbon): " . $createdAt->toISOString());
            $this->info("⏰ Diferença em horas: " . $hoursDiff);
            $this->info("⏳ Expira em: " . $expiresAt);

            if ($hoursDiff > 1) {
                $this->warn("⚠️ Token expirado (mais de 1 hora)");
            } else {
                $this->info("✅ Token válido (menos de 1 hora)");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro na lógica manual:");
            $this->error($e->getMessage());
            $this->error("Arquivo: " . $e->getFile() . ":" . $e->getLine());
        }

        // Limpar token de teste
        $passwordReset->delete();
        $this->info("🧹 Token de teste removido");

        return 0;
    }
}
