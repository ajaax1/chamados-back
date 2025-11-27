<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desabilitar verificação de foreign keys temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpar dados existentes
        TicketMessage::truncate();
        Ticket::truncate();
        User::where('email', '!=', 'ruanhigor123@gmail.com')->delete();
        
        // Reabilitar verificação de foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Criar usuários
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $support1 = User::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
            'role' => 'support',
        ]);

        $support2 = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password' => Hash::make('password123'),
            'role' => 'support',
        ]);

        $assistant1 = User::create([
            'name' => 'Pedro Costa',
            'email' => 'pedro@example.com',
            'password' => Hash::make('password123'),
            'role' => 'assistant',
        ]);

        $assistant2 = User::create([
            'name' => 'Ana Oliveira',
            'email' => 'ana@example.com',
            'password' => Hash::make('password123'),
            'role' => 'assistant',
        ]);

        // Criar clientes
        $clientes = [];
        for ($i = 1; $i <= 10; $i++) {
            $clientes[] = User::create([
                'name' => "Cliente {$i}",
                'email' => "cliente{$i}@example.com",
                'password' => Hash::make('password123'),
                'role' => 'cliente',
            ]);
        }

        $origens = ['formulario_web', 'email', 'api', 'tel_manual'];
        $statuses = ['aberto', 'pendente', 'resolvido', 'finalizado'];
        $priorities = ['baixa', 'média', 'alta'];
        $agents = [$support1, $support2, $assistant1, $assistant2, $admin];

        $tickets = [];
        $now = Carbon::now();
        
        // Criar tickets nos últimos 30 dias
        for ($day = 30; $day >= 0; $day--) {
            $date = $now->copy()->subDays($day);
            
            // Criar entre 2 e 8 tickets por dia
            $ticketsPerDay = rand(2, 8);
            
            for ($i = 0; $i < $ticketsPerDay; $i++) {
                $cliente = $clientes[array_rand($clientes)];
                $agent = $agents[array_rand($agents)];
                $origem = $origens[array_rand($origens)];
                $priority = $priorities[array_rand($priorities)];
                
                // Determinar status baseado na data
                $status = 'aberto';
                $tempoResolucao = null;
                $prazoResolucao = null;
                
                // 60% dos tickets antigos estão resolvidos
                if ($day > 5 && rand(1, 100) <= 60) {
                    $status = rand(1, 2) === 1 ? 'resolvido' : 'finalizado';
                    // Tempo de resolução entre 30 minutos e 5 dias
                    $tempoResolucao = rand(30, 7200); // minutos
                    $prazoResolucao = $date->copy()->addMinutes($tempoResolucao);
                } elseif ($day > 2 && rand(1, 100) <= 30) {
                    $status = 'pendente';
                }

                $ticket = Ticket::create([
                    'title' => $this->getRandomTitle(),
                    'nome_cliente' => $cliente->name,
                    'whatsapp_numero' => $this->getRandomPhone(),
                    'descricao' => $this->getRandomDescription(),
                    'user_id' => $agent->id,
                    'cliente_id' => $cliente->id,
                    'status' => $status,
                    'priority' => $priority,
                    'origem' => $origem,
                    'tempo_resolucao' => $tempoResolucao,
                    'prazo_resolucao' => $prazoResolucao,
                    'created_at' => $date->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59)),
                    'updated_at' => $status === 'resolvido' || $status === 'finalizado' 
                        ? $date->copy()->addDays(rand(1, 5))->addHours(rand(8, 18))
                        : $date->copy(),
                ]);

                $tickets[] = $ticket;

                // Criar mensagens para calcular tempo de resposta
                $this->createMessagesForTicket($ticket, $agent, $cliente, $date);
            }
        }

        $this->command->info('✅ Dados simulados criados com sucesso!');
        $this->command->info("✅ " . count($tickets) . " tickets criados");
        $this->command->info("✅ " . User::count() . " usuários criados");
        $this->command->info('');
        $this->command->info('📧 Credenciais (senha: password123):');
        $this->command->info('   Admin: admin@example.com');
        $this->command->info('   Support 1: joao@example.com');
        $this->command->info('   Support 2: maria@example.com');
        $this->command->info('   Assistant 1: pedro@example.com');
        $this->command->info('   Assistant 2: ana@example.com');
        $this->command->info('   Clientes: cliente1@example.com até cliente10@example.com');
    }

    private function createMessagesForTicket($ticket, $agent, $cliente, $createdAt)
    {
        $messages = [];
        
        // Primeira mensagem do cliente (criação do ticket)
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $cliente->id,
            'message' => $this->getClientMessage(),
            'is_internal' => false,
            'created_at' => $createdAt,
        ]);

        // Se o ticket foi resolvido, criar mensagens de interação
        if (in_array($ticket->status, ['resolvido', 'finalizado'])) {
            // Primeira resposta do agente (entre 15 minutos e 4 horas depois)
            $firstResponseTime = $createdAt->copy()->addMinutes(rand(15, 240));
            
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => $this->getAgentResponse(),
                'is_internal' => false,
                'created_at' => $firstResponseTime,
            ]);

            // Resposta do cliente (entre 30 minutos e 2 horas depois)
            if (rand(1, 100) <= 70) {
                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $cliente->id,
                    'message' => $this->getClientReply(),
                    'is_internal' => false,
                    'created_at' => $firstResponseTime->copy()->addMinutes(rand(30, 120)),
                ]);

                // Resposta final do agente (resolução)
                if (rand(1, 100) <= 80) {
                    TicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $agent->id,
                        'message' => $this->getResolutionMessage(),
                        'is_internal' => false,
                        'created_at' => $ticket->updated_at->copy()->subMinutes(rand(5, 30)),
                    ]);
                }
            }
        } elseif ($ticket->status === 'pendente') {
            // Ticket pendente - apenas primeira resposta
            $firstResponseTime = $createdAt->copy()->addMinutes(rand(30, 480));
            
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => $this->getPendingMessage(),
                'is_internal' => false,
                'created_at' => $firstResponseTime,
            ]);
        } elseif (rand(1, 100) <= 50) {
            // 50% dos tickets abertos têm primeira resposta
            $firstResponseTime = $createdAt->copy()->addMinutes(rand(60, 720));
            
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => $this->getAgentResponse(),
                'is_internal' => false,
                'created_at' => $firstResponseTime,
            ]);
        }

        // Algumas mensagens internas (10% dos tickets)
        if (rand(1, 100) <= 10) {
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => 'Nota interna: ' . $this->getInternalNote(),
                'is_internal' => true,
                'created_at' => $createdAt->copy()->addHours(rand(1, 24)),
            ]);
        }
    }

    private function getRandomTitle()
    {
        $titles = [
            'Problema no login',
            'Erro ao processar pagamento',
            'Sistema lento',
            'Não consigo acessar minha conta',
            'Dúvida sobre funcionalidade',
            'Erro ao enviar formulário',
            'Preciso de ajuda com configuração',
            'Problema com relatório',
            'Solicitação de nova funcionalidade',
            'Bug na interface',
            'Dúvida sobre integração',
            'Problema com notificações',
            'Erro ao exportar dados',
            'Sistema não responde',
            'Preciso resetar senha',
        ];
        return $titles[array_rand($titles)];
    }

    private function getRandomDescription()
    {
        $descriptions = [
            'Estou tendo problemas para acessar o sistema. A página não carrega.',
            'O sistema está muito lento hoje. Demora muito para carregar as páginas.',
            'Não consigo fazer login. A senha não está funcionando.',
            'Preciso de ajuda para configurar minha conta.',
            'Encontrei um erro ao tentar processar um pagamento.',
            'O formulário não está salvando os dados corretamente.',
            'Gostaria de solicitar uma nova funcionalidade.',
            'Há um bug na interface que está impedindo o uso.',
            'Preciso de informações sobre como integrar com nossa API.',
            'As notificações não estão chegando.',
        ];
        return $descriptions[array_rand($descriptions)];
    }

    private function getRandomPhone()
    {
        return '(' . rand(11, 99) . ') ' . rand(90000, 99999) . '-' . rand(1000, 9999);
    }

    private function getClientMessage()
    {
        $messages = [
            'Olá, preciso de ajuda com um problema.',
            'Boa tarde, estou com uma dúvida.',
            'Preciso resolver uma questão urgente.',
            'Olá, encontrei um problema no sistema.',
            'Bom dia, gostaria de solicitar suporte.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getAgentResponse()
    {
        $messages = [
            'Olá! Obrigado por entrar em contato. Vou analisar seu caso.',
            'Boa tarde! Entendi seu problema. Vou verificar e retorno em breve.',
            'Olá! Recebi sua solicitação. Estou trabalhando nisso.',
            'Bom dia! Vou investigar a questão e te retorno o mais rápido possível.',
            'Olá! Entendi sua necessidade. Vou resolver isso para você.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getClientReply()
    {
        $messages = [
            'Obrigado pela resposta! Aguardo retorno.',
            'Entendi, obrigado!',
            'Perfeito, aguardo a solução.',
            'Ok, muito obrigado!',
            'Entendido, aguardo retorno.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getResolutionMessage()
    {
        $messages = [
            'Problema resolvido! Se precisar de mais alguma coisa, estou à disposição.',
            'Questão solucionada. Caso tenha mais dúvidas, pode me chamar.',
            'Resolvido! Espero ter ajudado.',
            'Problema corrigido. Qualquer coisa, estou aqui.',
            'Tudo certo! Se precisar de mais alguma coisa, me avise.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getPendingMessage()
    {
        $messages = [
            'Olá! Estou analisando seu caso. Retorno em breve.',
            'Recebi sua solicitação. Vou verificar e te retorno.',
            'Olá! Estou trabalhando nisso. Em breve teremos uma solução.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getInternalNote()
    {
        $notes = [
            'Cliente precisa de atenção especial.',
            'Verificar com o time técnico.',
            'Priorizar este caso.',
            'Cliente VIP - dar prioridade.',
            'Aguardar resposta do desenvolvedor.',
        ];
        return $notes[array_rand($notes)];
    }
}
