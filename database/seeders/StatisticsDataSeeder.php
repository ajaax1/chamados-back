<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketAttachment;
use App\Models\MessageAttachment;
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
        $this->command->info('🔄 Limpando dados existentes...');
        
        // Desabilitar verificação de foreign keys temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpar dados existentes
        MessageAttachment::truncate();
        TicketAttachment::truncate();
        TicketMessage::truncate();
        Ticket::truncate();
        User::where('email', '!=', 'ruanhigor123@gmail.com')->delete();
        
        // Reabilitar verificação de foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('👥 Criando usuários...');
        
        // Criar administradores
        $admins = [];
        $adminNames = [
            'Carlos Eduardo Silva',
            'Patrícia Almeida',
            'Roberto Fernandes'
        ];
        foreach ($adminNames as $index => $name) {
            $admins[] = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '', $name)) . '@empresa.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'created_at' => Carbon::now()->subMonths(rand(6, 12)),
            ]);
        }

        // Criar suportes (8 suportes com diferentes níveis de produtividade)
        $supports = [];
        $supportNames = [
            'João Pedro Santos',
            'Maria Fernanda Costa',
            'Lucas Oliveira',
            'Ana Paula Rodrigues',
            'Rafael Souza',
            'Juliana Lima',
            'Fernando Alves',
            'Camila Martins'
        ];
        foreach ($supportNames as $index => $name) {
            $supports[] = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '', $name)) . '@empresa.com',
                'password' => Hash::make('password123'),
                'role' => 'support',
                'created_at' => Carbon::now()->subMonths(rand(3, 10)),
            ]);
        }

        // Criar assistentes (5 assistentes)
        $assistants = [];
        $assistantNames = [
            'Pedro Henrique',
            'Beatriz Silva',
            'Thiago Pereira',
            'Mariana Santos',
            'Gabriel Costa'
        ];
        foreach ($assistantNames as $index => $name) {
            $assistants[] = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '', $name)) . '@empresa.com',
                'password' => Hash::make('password123'),
                'role' => 'assistant',
                'created_at' => Carbon::now()->subMonths(rand(2, 8)),
            ]);
        }

        // Criar clientes (25 clientes com nomes brasileiros realistas)
        $clientes = [];
        $clienteNames = [
            'Roberto Carlos Mendes',
            'Silvia Regina Alves',
            'Marcos Antônio Ferreira',
            'Cristina dos Santos',
            'Paulo César Lima',
            'Fernanda Oliveira',
            'Ricardo Nunes',
            'Luciana Barbosa',
            'André Luiz Silva',
            'Renata Costa',
            'Fábio Henrique',
            'Priscila Martins',
            'Gustavo Pereira',
            'Vanessa Rodrigues',
            'Bruno Almeida',
            'Tatiana Souza',
            'Leandro Campos',
            'Daniela Freitas',
            'Rodrigo Teixeira',
            'Amanda Rocha',
            'Eduardo Santos',
            'Carolina Dias',
            'Felipe Araújo',
            'Larissa Monteiro',
            'Thiago Ribeiro'
        ];
        foreach ($clienteNames as $index => $name) {
            $clientes[] = User::create([
                'name' => $name,
                'email' => 'cliente' . ($index + 1) . '@exemplo.com',
                'password' => Hash::make('password123'),
                'role' => 'cliente',
                'created_at' => Carbon::now()->subMonths(rand(1, 18)),
            ]);
        }

        // Incluir o usuário principal (ruanhigor123@gmail.com) se existir
        $mainUser = User::where('email', 'ruanhigor123@gmail.com')->first();
        if ($mainUser && $mainUser->role === 'admin') {
            $admins[] = $mainUser;
        }
        
        $allAgents = array_merge($supports, $assistants, $admins);
        
        $this->command->info('🎫 Criando tickets e mensagens...');
        
        $origens = ['formulario_web', 'email', 'api', 'tel_manual'];
        $origemWeights = [40, 30, 20, 10]; // formulario_web mais comum
        
        $priorities = ['baixa', 'média', 'alta'];
        $priorityWeights = [30, 50, 20]; // média mais comum
        
        $tickets = [];
        $now = Carbon::now();
        
        // Criar tickets nos últimos 60 dias com variação realista
        for ($day = 60; $day >= 0; $day--) {
            $date = $now->copy()->subDays($day);
            $dayOfWeek = $date->dayOfWeek; // 0 = domingo, 6 = sábado
            
            // Menos tickets em finais de semana
            if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                $ticketsPerDay = rand(1, 4);
            } else {
                // Mais tickets em dias úteis, com variação
                $ticketsPerDay = rand(5, 15);
            }
            
            for ($i = 0; $i < $ticketsPerDay; $i++) {
                $cliente = $clientes[array_rand($clientes)];
                
                // Distribuição ponderada de agentes (alguns mais produtivos)
                $agentWeights = [];
                foreach ($allAgents as $idx => $agent) {
                    // Suportes recebem mais tickets (peso maior)
                    if ($agent->role === 'support') {
                        $agentWeights[$idx] = 3;
                    } elseif ($agent->role === 'assistant') {
                        $agentWeights[$idx] = 2;
                    } else {
                        $agentWeights[$idx] = 1; // Admins recebem menos
                    }
                }
                
                $agent = $this->weightedRandom($allAgents, $agentWeights);
                
                // Origem ponderada
                $origem = $this->weightedRandom($origens, $origemWeights);
                
                // Prioridade ponderada
                $priority = $this->weightedRandom($priorities, $priorityWeights);
                
                // Determinar status baseado na data e probabilidade
                $status = 'aberto';
                $tempoResolucao = null;
                $prazoResolucao = null;
                $resolvidoEm = null;
                
                $daysAgo = $day;
                
                // Tickets mais antigos têm maior probabilidade de estar resolvidos
                if ($daysAgo > 7) {
                    $resolvedChance = min(85, 40 + ($daysAgo * 2)); // Até 85% para tickets muito antigos
                    if (rand(1, 100) <= $resolvedChance) {
                        $status = rand(1, 2) === 1 ? 'resolvido' : 'finalizado';
                        // Tempo de resolução realista: 30 minutos a 7 dias
                        $tempoResolucao = rand(30, 10080); // minutos
                        $resolvidoEm = $date->copy()->addMinutes($tempoResolucao);
                        $prazoResolucao = $resolvidoEm;
                    } elseif (rand(1, 100) <= 25) {
                        $status = 'pendente';
                    }
                } elseif ($daysAgo > 2) {
                    if (rand(1, 100) <= 50) {
                        $status = rand(1, 2) === 1 ? 'resolvido' : 'finalizado';
                        $tempoResolucao = rand(30, 2880); // até 2 dias
                        $resolvidoEm = $date->copy()->addMinutes($tempoResolucao);
                        $prazoResolucao = $resolvidoEm;
                    } elseif (rand(1, 100) <= 30) {
                        $status = 'pendente';
                    }
                } elseif (rand(1, 100) <= 20) {
                    $status = 'pendente';
                }

                // Horário comercial (8h às 18h) com mais concentração no período da manhã e tarde
                $hour = rand(8, 17);
                if ($hour < 10) {
                    $hour = rand(8, 10); // Manhã cedo
                } elseif ($hour > 16) {
                    $hour = rand(16, 18); // Final da tarde
                }
                
                $createdAt = $date->copy()->setTime($hour, rand(0, 59), rand(0, 59));

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
                    'created_at' => $createdAt,
                    'updated_at' => $status === 'resolvido' || $status === 'finalizado' 
                        ? ($resolvidoEm ?? $createdAt->copy()->addDays(rand(1, 3))->setTime(rand(9, 17), rand(0, 59)))
                        : ($status === 'pendente' ? $createdAt->copy()->addHours(rand(2, 24)) : $createdAt),
                ]);

                $tickets[] = $ticket;

                // Criar mensagens e anexos para o ticket
                $this->createMessagesForTicket($ticket, $agent, $cliente, $createdAt, $resolvidoEm);
            }
            
            // Mostrar progresso a cada 10 dias
            if ($day % 10 == 0) {
                $this->command->info("   Processados " . (60 - $day) . " dias...");
            }
        }

        $this->command->info('');
        $this->command->info('✅ Dados simulados criados com sucesso!');
        $this->command->info("✅ " . count($tickets) . " tickets criados");
        $this->command->info("✅ " . TicketMessage::count() . " mensagens criadas");
        $this->command->info("✅ " . TicketAttachment::count() . " anexos de tickets criados");
        $this->command->info("✅ " . MessageAttachment::count() . " anexos de mensagens criados");
        $this->command->info("✅ " . User::count() . " usuários criados");
        $this->command->info('');
        $this->command->info('📧 Credenciais de teste (senha: password123):');
        $this->command->info('');
        $this->command->info('   👨‍💼 Administradores:');
        foreach ($admins as $admin) {
            $this->command->info("      - {$admin->name}: {$admin->email}");
        }
        $this->command->info('');
        $this->command->info('   👨‍💻 Suportes:');
        foreach (array_slice($supports, 0, 5) as $support) {
            $this->command->info("      - {$support->name}: {$support->email}");
        }
        $this->command->info('      ... e mais ' . (count($supports) - 5) . ' suportes');
        $this->command->info('');
        $this->command->info('   👨‍🔧 Assistentes:');
        foreach (array_slice($assistants, 0, 3) as $assistant) {
            $this->command->info("      - {$assistant->name}: {$assistant->email}");
        }
        $this->command->info('      ... e mais ' . (count($assistants) - 3) . ' assistentes');
        $this->command->info('');
        $this->command->info('   👤 Clientes: cliente1@exemplo.com até cliente25@exemplo.com');
    }

    private function weightedRandom($items, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        $currentWeight = 0;
        
        foreach ($items as $index => $item) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $item;
            }
        }
        
        return $items[0]; // Fallback
    }

    private function createMessagesForTicket($ticket, $agent, $cliente, $createdAt, $resolvidoEm = null)
    {
        // Primeira mensagem do cliente (criação do ticket)
        $firstMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $cliente->id,
            'message' => $this->getClientMessage(),
            'is_internal' => false,
            'created_at' => $createdAt,
        ]);

        // 20% de chance de anexo na primeira mensagem do cliente
        if (rand(1, 100) <= 20) {
            $this->createMessageAttachment($firstMessage, 'cliente');
        }

        // Se o ticket foi resolvido, criar mensagens de interação realistas
        if (in_array($ticket->status, ['resolvido', 'finalizado'])) {
            // Primeira resposta do agente (entre 10 minutos e 6 horas depois)
            // Agentes mais rápidos respondem mais rápido
            $responseTimeMinutes = rand(10, 360);
            $firstResponseTime = $createdAt->copy()->addMinutes($responseTimeMinutes);
            
            $agentFirstMessage = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => $this->getAgentResponse(),
                'is_internal' => false,
                'created_at' => $firstResponseTime,
            ]);

            // 15% de chance de anexo na primeira resposta do agente
            if (rand(1, 100) <= 15) {
                $this->createMessageAttachment($agentFirstMessage, 'agente');
            }

            // Resposta do cliente (entre 15 minutos e 4 horas depois)
            if (rand(1, 100) <= 75) {
                $clientReplyTime = $firstResponseTime->copy()->addMinutes(rand(15, 240));
                
                $clientReplyMessage = TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $cliente->id,
                    'message' => $this->getClientReply(),
                    'is_internal' => false,
                    'created_at' => $clientReplyTime,
                ]);

                // Resposta final do agente (resolução) - antes da data de resolução
                if (rand(1, 100) <= 85) {
                    $resolutionMessageTime = $resolvidoEm 
                        ? $resolvidoEm->copy()->subMinutes(rand(5, 60))
                        : $clientReplyTime->copy()->addMinutes(rand(30, 180));
                    
                    $resolutionMessage = TicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $agent->id,
                        'message' => $this->getResolutionMessage(),
                        'is_internal' => false,
                        'created_at' => $resolutionMessageTime,
                    ]);

                    // 10% de chance de anexo na mensagem de resolução
                    if (rand(1, 100) <= 10) {
                        $this->createMessageAttachment($resolutionMessage, 'agente');
                    }
                }
            } else {
                // Cliente não respondeu, agente resolve diretamente
                $resolutionMessageTime = $resolvidoEm 
                    ? $resolvidoEm->copy()->subMinutes(rand(5, 60))
                    : $firstResponseTime->copy()->addHours(rand(2, 8));
                
                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $agent->id,
                    'message' => $this->getResolutionMessage(),
                    'is_internal' => false,
                    'created_at' => $resolutionMessageTime,
                ]);
            }

            // Mensagens intermediárias (30% dos tickets resolvidos têm mais de 3 mensagens)
            if (rand(1, 100) <= 30) {
                $intermediateCount = rand(1, 3);
                $lastMessageTime = $firstResponseTime;
                
                for ($i = 0; $i < $intermediateCount; $i++) {
                    $isFromAgent = rand(1, 2) === 1;
                    $lastMessageTime = $lastMessageTime->copy()->addMinutes(rand(30, 180));
                    
                    TicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $isFromAgent ? $agent->id : $cliente->id,
                        'message' => $isFromAgent ? $this->getAgentFollowUp() : $this->getClientFollowUp(),
                        'is_internal' => false,
                        'created_at' => $lastMessageTime,
                    ]);
                }
            }
        } elseif ($ticket->status === 'pendente') {
            // Ticket pendente - primeira resposta do agente (entre 30 minutos e 8 horas depois)
            $firstResponseTime = $createdAt->copy()->addMinutes(rand(30, 480));
            
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => $this->getPendingMessage(),
                'is_internal' => false,
                'created_at' => $firstResponseTime,
            ]);
        } elseif (rand(1, 100) <= 40) {
            // 40% dos tickets abertos têm primeira resposta (mas ainda não resolvidos)
            $firstResponseTime = $createdAt->copy()->addMinutes(rand(60, 1440)); // até 24 horas
            
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => $this->getAgentResponse(),
                'is_internal' => false,
                'created_at' => $firstResponseTime,
            ]);
        }

        // Mensagens internas (15% dos tickets têm notas internas)
        if (rand(1, 100) <= 15) {
            $internalMessageTime = $createdAt->copy()->addHours(rand(1, 48));
            
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
                'message' => 'Nota interna: ' . $this->getInternalNote(),
                'is_internal' => true,
                'created_at' => $internalMessageTime,
            ]);
        }

        // 10% dos tickets têm anexos diretos no ticket
        if (rand(1, 100) <= 10) {
            $this->createTicketAttachment($ticket);
        }
    }

    private function createTicketAttachment($ticket)
    {
        $fileTypes = [
            ['nome' => 'print_erro.png', 'mime' => 'image/png', 'size' => rand(50000, 500000)],
            ['nome' => 'log_sistema.txt', 'mime' => 'text/plain', 'size' => rand(10000, 100000)],
            ['nome' => 'documento.pdf', 'mime' => 'application/pdf', 'size' => rand(100000, 2000000)],
            ['nome' => 'screenshot.jpg', 'mime' => 'image/jpeg', 'size' => rand(100000, 800000)],
        ];
        
        $file = $fileTypes[array_rand($fileTypes)];
        
        TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'nome_arquivo' => $file['nome'],
            'caminho_arquivo' => 'attachments/tickets/' . $ticket->id . '/' . $file['nome'],
            'tipo_mime' => $file['mime'],
            'tamanho' => $file['size'],
            'created_at' => $ticket->created_at->copy()->addMinutes(rand(0, 60)),
        ]);
    }

    private function createMessageAttachment($message, $type = 'cliente')
    {
        $fileTypes = [
            ['nome' => 'anexo.png', 'mime' => 'image/png', 'size' => rand(50000, 500000)],
            ['nome' => 'arquivo.pdf', 'mime' => 'application/pdf', 'size' => rand(100000, 2000000)],
            ['nome' => 'imagem.jpg', 'mime' => 'image/jpeg', 'size' => rand(100000, 800000)],
            ['nome' => 'documento.docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'size' => rand(50000, 500000)],
        ];
        
        $file = $fileTypes[array_rand($fileTypes)];
        
        MessageAttachment::create([
            'ticket_message_id' => $message->id,
            'nome_arquivo' => $file['nome'],
            'caminho_arquivo' => 'attachments/messages/' . $message->id . '/' . $file['nome'],
            'tipo_mime' => $file['mime'],
            'tamanho' => $file['size'],
            'created_at' => $message->created_at,
        ]);
    }

    private function getRandomTitle()
    {
        $titles = [
            'Problema no login do sistema',
            'Erro ao processar pagamento',
            'Sistema muito lento',
            'Não consigo acessar minha conta',
            'Dúvida sobre funcionalidade X',
            'Erro ao enviar formulário de contato',
            'Preciso de ajuda com configuração',
            'Problema ao gerar relatório',
            'Solicitação de nova funcionalidade',
            'Bug na interface do usuário',
            'Dúvida sobre integração com API',
            'Problema com notificações por email',
            'Erro ao exportar dados para Excel',
            'Sistema não responde após clicar em salvar',
            'Preciso resetar minha senha',
            'Dúvida sobre planos e preços',
            'Problema com upload de arquivos',
            'Erro 500 ao acessar página',
            'Dúvida sobre cancelamento de assinatura',
            'Problema com integração do WhatsApp',
            'Não recebo emails de confirmação',
            'Erro ao fazer backup dos dados',
            'Dúvida sobre permissões de usuário',
            'Problema com relatório de vendas',
            'Sistema trava ao processar grande volume',
        ];
        return $titles[array_rand($titles)];
    }

    private function getRandomDescription()
    {
        $descriptions = [
            'Estou tendo problemas para acessar o sistema. Quando tento fazer login, a página não carrega e fica em branco.',
            'O sistema está muito lento hoje. Demora mais de 30 segundos para carregar qualquer página. Isso está atrapalhando meu trabalho.',
            'Não consigo fazer login. Digito minha senha corretamente mas aparece mensagem de erro. Já tentei resetar a senha mas não recebi o email.',
            'Preciso de ajuda para configurar minha conta. Não consigo encontrar onde alterar minhas informações pessoais.',
            'Encontrei um erro ao tentar processar um pagamento. O sistema mostra mensagem de erro mas não diz qual é o problema.',
            'O formulário de contato não está salvando os dados corretamente. Quando preencho e clico em enviar, nada acontece.',
            'Gostaria de solicitar uma nova funcionalidade. Seria muito útil ter a opção de exportar relatórios em formato PDF.',
            'Há um bug na interface que está impedindo o uso. Quando clico no botão de salvar, o sistema fecha a página.',
            'Preciso de informações sobre como integrar com nossa API. Gostaria de saber quais endpoints estão disponíveis.',
            'As notificações não estão chegando. Configurei para receber por email mas não recebo nenhuma notificação.',
            'O sistema está apresentando erro 500 quando tento acessar a página de relatórios. Isso começou a acontecer ontem.',
            'Não consigo fazer upload de arquivos. Quando tento enviar uma imagem, aparece mensagem de erro dizendo que o arquivo é muito grande.',
            'Preciso de ajuda para entender como funcionam as permissões de usuário. Quero dar acesso limitado para alguns colaboradores.',
            'O relatório de vendas não está mostrando os dados corretos. Os valores estão diferentes do que vejo no sistema.',
            'Quando processamos um grande volume de dados, o sistema trava e precisa ser reiniciado. Isso acontece frequentemente.',
        ];
        return $descriptions[array_rand($descriptions)];
    }

    private function getRandomPhone()
    {
        $ddds = ['11', '21', '31', '41', '47', '48', '51', '61', '71', '81', '85'];
        $ddd = $ddds[array_rand($ddds)];
        $firstPart = rand(90000, 99999);
        $secondPart = rand(1000, 9999);
        return "({$ddd}) {$firstPart}-{$secondPart}";
    }

    private function getClientMessage()
    {
        $messages = [
            'Olá, preciso de ajuda com um problema que estou enfrentando.',
            'Boa tarde, estou com uma dúvida sobre o sistema.',
            'Preciso resolver uma questão urgente. Podem me ajudar?',
            'Olá, encontrei um problema no sistema e gostaria de reportar.',
            'Bom dia, gostaria de solicitar suporte técnico.',
            'Oi, estou tendo dificuldades para usar uma funcionalidade.',
            'Olá, preciso de orientação sobre como fazer algo no sistema.',
            'Boa tarde, encontrei um erro e preciso de ajuda para resolver.',
            'Preciso de suporte urgente. O sistema não está funcionando.',
            'Olá, gostaria de tirar algumas dúvidas sobre o sistema.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getAgentResponse()
    {
        $messages = [
            'Olá! Obrigado por entrar em contato. Vou analisar seu caso e retorno em breve.',
            'Boa tarde! Entendi seu problema. Estou verificando e em breve te retorno com uma solução.',
            'Olá! Recebi sua solicitação. Estou trabalhando nisso e te mantenho informado.',
            'Bom dia! Vou investigar a questão que você relatou e retorno o mais rápido possível.',
            'Olá! Entendi sua necessidade. Vou resolver isso para você o quanto antes.',
            'Oi! Obrigado pelo contato. Estou analisando o problema e em breve teremos uma solução.',
            'Boa tarde! Recebi sua mensagem. Vou verificar o que está acontecendo e te retorno.',
            'Olá! Entendi o problema. Deixe-me investigar e retorno com mais informações.',
            'Bom dia! Vou trabalhar na sua solicitação e te mantenho atualizado sobre o progresso.',
            'Oi! Obrigado por reportar. Estou verificando e em breve te retorno.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getClientReply()
    {
        $messages = [
            'Obrigado pela resposta! Aguardo retorno.',
            'Entendi, obrigado pela atenção!',
            'Perfeito, aguardo a solução.',
            'Ok, muito obrigado pela ajuda!',
            'Entendido, aguardo retorno.',
            'Obrigado! Fico no aguardo.',
            'Perfeito, obrigado!',
            'Entendi, aguardo novidades.',
            'Ok, muito obrigado!',
            'Agradeço a atenção, aguardo retorno.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getAgentFollowUp()
    {
        $messages = [
            'Estou verificando isso para você. Em breve retorno.',
            'Ainda estou trabalhando nisso. Qualquer novidade te aviso.',
            'Preciso de mais algumas informações. Você pode me ajudar?',
            'Estou aguardando resposta do time técnico. Te mantenho informado.',
            'Verifiquei e encontrei o problema. Estou resolvendo agora.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getClientFollowUp()
    {
        $messages = [
            'Ainda estou com o problema. Pode verificar novamente?',
            'Obrigado, mas ainda não funcionou. O que mais posso fazer?',
            'Entendi, mas preciso de mais detalhes sobre a solução.',
            'Ok, mas tenho outra dúvida relacionada.',
            'Ainda não consegui resolver. Pode me ajudar mais?',
        ];
        return $messages[array_rand($messages)];
    }

    private function getResolutionMessage()
    {
        $messages = [
            'Problema resolvido! Se precisar de mais alguma coisa, estou à disposição.',
            'Questão solucionada. Caso tenha mais dúvidas, pode me chamar.',
            'Resolvido! Espero ter ajudado. Qualquer coisa, estou aqui.',
            'Problema corrigido. Qualquer dúvida adicional, me avise.',
            'Tudo certo! Se precisar de mais alguma coisa, me avise.',
            'Problema resolvido com sucesso! Fico feliz em ter ajudado.',
            'Questão solucionada. Espero que tudo esteja funcionando agora.',
            'Resolvido! Se tiver mais alguma necessidade, estou à disposição.',
            'Problema corrigido. Obrigado pela paciência!',
            'Tudo certo agora! Se precisar de mais ajuda, pode me chamar.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getPendingMessage()
    {
        $messages = [
            'Olá! Estou analisando seu caso. Retorno em breve com uma solução.',
            'Recebi sua solicitação. Vou verificar e te retorno o mais rápido possível.',
            'Olá! Estou trabalhando nisso. Em breve teremos uma resposta para você.',
            'Recebi sua mensagem. Estou investigando e retorno em breve.',
            'Olá! Estou analisando o problema. Te mantenho informado sobre o progresso.',
        ];
        return $messages[array_rand($messages)];
    }

    private function getInternalNote()
    {
        $notes = [
            'Cliente precisa de atenção especial - caso VIP.',
            'Verificar com o time técnico antes de responder.',
            'Priorizar este caso - cliente insatisfeito.',
            'Cliente VIP - dar prioridade máxima.',
            'Aguardar resposta do desenvolvedor sobre o problema.',
            'Cliente reportou problema similar anteriormente.',
            'Verificar histórico de tickets deste cliente.',
            'Caso complexo - pode precisar de escalação.',
            'Cliente está muito insatisfeito - atenção redobrada.',
            'Problema já foi reportado por outros clientes.',
        ];
        return $notes[array_rand($notes)];
    }
}
