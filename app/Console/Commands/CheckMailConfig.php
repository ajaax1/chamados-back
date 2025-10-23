<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckMailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:check-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check email configuration and test connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔧 Verificando Configurações de Email");
        $this->line('');

        // Verificar variáveis de ambiente
        $this->info("📋 Variáveis de Ambiente:");
        $this->table(
            ['Configuração', 'Valor', 'Status'],
            [
                ['MAIL_MAILER', env('MAIL_MAILER', 'não definido'), env('MAIL_MAILER') ? '✅' : '❌'],
                ['MAIL_HOST', env('MAIL_HOST', 'não definido'), env('MAIL_HOST') ? '✅' : '❌'],
                ['MAIL_PORT', env('MAIL_PORT', 'não definido'), env('MAIL_PORT') ? '✅' : '❌'],
                ['MAIL_USERNAME', env('MAIL_USERNAME', 'não definido'), env('MAIL_USERNAME') ? '✅' : '❌'],
                ['MAIL_PASSWORD', env('MAIL_PASSWORD') ? '***definido***' : 'não definido', env('MAIL_PASSWORD') ? '✅' : '❌'],
                ['MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'não definido'), env('MAIL_ENCRYPTION') ? '✅' : '❌'],
                ['MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'não definido'), env('MAIL_FROM_ADDRESS') ? '✅' : '❌'],
                ['MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'não definido'), env('MAIL_FROM_NAME') ? '✅' : '❌'],
            ]
        );

        $this->line('');

        // Verificar configurações do Laravel
        $this->info("⚙️ Configurações do Laravel:");
        $this->table(
            ['Configuração', 'Valor'],
            [
                ['Driver', config('mail.default')],
                ['Host', config('mail.mailers.smtp.host')],
                ['Port', config('mail.mailers.smtp.port')],
                ['Encryption', config('mail.mailers.smtp.encryption')],
                ['Username', config('mail.mailers.smtp.username')],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
            ]
        );

        $this->line('');

        // Testar conexão SMTP
        $this->info("🔌 Testando Conexão SMTP...");
        try {
            $transport = Mail::getSwiftMailer()->getTransport();
            $transport->start();
            $transport->stop();
            $this->info("✅ Conexão SMTP bem-sucedida!");
        } catch (\Exception $e) {
            $this->error("❌ Erro na conexão SMTP:");
            $this->error($e->getMessage());
        }

        $this->line('');

        // Verificar template de email
        $this->info("📧 Verificando Template de Email...");
        $templatePath = resource_path('views/emails/password-reset.blade.php');
        if (file_exists($templatePath)) {
            $this->info("✅ Template encontrado: {$templatePath}");
        } else {
            $this->error("❌ Template não encontrado: {$templatePath}");
        }

        $this->line('');

        // Sugestões de troubleshooting
        $this->info("💡 Sugestões de Troubleshooting:");
        $this->line("1. Verifique se o arquivo .env está configurado corretamente");
        $this->line("2. Execute: php artisan config:clear");
        $this->line("3. Verifique os logs: tail -f storage/logs/laravel.log");
        $this->line("4. Teste o envio: php artisan test:email admin@example.com");
        $this->line("5. Verifique se as credenciais SMTP estão corretas");
        $this->line("6. Teste a conectividade: telnet smtp.titan.email 465");

        return 0;
    }
}
