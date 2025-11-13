<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar 5 usuários
        $users = [
            [
                'name' => 'Admin Silva',
                'email' => 'admin@teste.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Suporte Costa',
                'email' => 'suporte@teste.com',
                'password' => Hash::make('password123'),
                'role' => 'support',
            ],
            [
                'name' => 'Assistente Santos',
                'email' => 'assistente@teste.com',
                'password' => Hash::make('password123'),
                'role' => 'assistant',
            ],
            [
                'name' => 'Cliente João',
                'email' => 'cliente1@teste.com',
                'password' => Hash::make('password123'),
                'role' => 'cliente',
            ],
            [
                'name' => 'Cliente Maria',
                'email' => 'cliente2@teste.com',
                'password' => Hash::make('password123'),
                'role' => 'cliente',
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[] = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $admin = $createdUsers[0];
        $support = $createdUsers[1];
        $assistant = $createdUsers[2];
        $cliente1 = $createdUsers[3];
        $cliente2 = $createdUsers[4];

        // Criar 20 tickets
        $tickets = [
            // Tickets abertos
            [
                'title' => 'Problema com login no sistema',
                'nome_cliente' => 'João Silva',
                'whatsapp_numero' => '5511999999999',
                'user_id' => $support->id,
                'cliente_id' => $cliente1->id,
                'descricao' => 'Não consigo fazer login no sistema. A senha não está funcionando.',
                'status' => 'aberto',
                'priority' => 'alta',
            ],
            [
                'title' => 'Erro ao enviar mensagem',
                'nome_cliente' => 'Maria Santos',
                'whatsapp_numero' => '5511888888888',
                'user_id' => $assistant->id,
                'cliente_id' => $cliente2->id,
                'descricao' => 'Ao tentar enviar uma mensagem, aparece um erro 500.',
                'status' => 'aberto',
                'priority' => 'alta',
            ],
            [
                'title' => 'Dúvida sobre funcionalidade',
                'nome_cliente' => 'Pedro Oliveira',
                'whatsapp_numero' => '5511777777777',
                'user_id' => $assistant->id,
                'cliente_id' => null,
                'descricao' => 'Como faço para anexar arquivos nos chamados?',
                'status' => 'aberto',
                'priority' => 'média',
            ],
            [
                'title' => 'Solicitação de nova feature',
                'nome_cliente' => 'Ana Costa',
                'whatsapp_numero' => '5511666666666',
                'user_id' => $admin->id,
                'cliente_id' => null,
                'descricao' => 'Gostaria de solicitar a adição de notificações por email.',
                'status' => 'aberto',
                'priority' => 'baixa',
            ],
            [
                'title' => 'Problema com upload de arquivo',
                'nome_cliente' => 'Carlos Mendes',
                'whatsapp_numero' => '5511555555555',
                'user_id' => $support->id,
                'cliente_id' => $cliente1->id,
                'descricao' => 'Não consigo fazer upload de arquivos PDF maiores que 5MB.',
                'status' => 'aberto',
                'priority' => 'média',
            ],

            // Tickets pendentes
            [
                'title' => 'Atualização de perfil',
                'nome_cliente' => 'Fernanda Lima',
                'whatsapp_numero' => '5511444444444',
                'user_id' => $assistant->id,
                'cliente_id' => $cliente2->id,
                'descricao' => 'Preciso atualizar meus dados cadastrais.',
                'status' => 'pendente',
                'priority' => 'baixa',
            ],
            [
                'title' => 'Relatório não está gerando',
                'nome_cliente' => 'Roberto Alves',
                'whatsapp_numero' => '5511333333333',
                'user_id' => $support->id,
                'cliente_id' => null,
                'descricao' => 'Ao tentar gerar o relatório mensal, o sistema trava.',
                'status' => 'pendente',
                'priority' => 'alta',
            ],
            [
                'title' => 'Dúvida sobre permissões',
                'nome_cliente' => 'Juliana Rocha',
                'whatsapp_numero' => '5511222222222',
                'user_id' => $admin->id,
                'cliente_id' => $cliente1->id,
                'descricao' => 'Quais são as permissões de cada role no sistema?',
                'status' => 'pendente',
                'priority' => 'média',
            ],
            [
                'title' => 'Problema com filtros',
                'nome_cliente' => 'Lucas Pereira',
                'whatsapp_numero' => '5511111111111',
                'user_id' => $assistant->id,
                'cliente_id' => null,
                'descricao' => 'Os filtros de busca não estão funcionando corretamente.',
                'status' => 'pendente',
                'priority' => 'média',
            ],
            [
                'title' => 'Solicitação de acesso',
                'nome_cliente' => 'Patricia Souza',
                'whatsapp_numero' => '5511999888777',
                'user_id' => $admin->id,
                'cliente_id' => $cliente2->id,
                'descricao' => 'Preciso de acesso ao sistema para minha equipe.',
                'status' => 'pendente',
                'priority' => 'alta',
            ],

            // Tickets resolvidos
            [
                'title' => 'Erro 404 em página',
                'nome_cliente' => 'Marcos Ferreira',
                'whatsapp_numero' => '5511999777666',
                'user_id' => $support->id,
                'cliente_id' => null,
                'descricao' => 'A página de configurações está retornando erro 404.',
                'status' => 'resolvido',
                'priority' => 'alta',
            ],
            [
                'title' => 'Problema com autenticação',
                'nome_cliente' => 'Camila Rodrigues',
                'whatsapp_numero' => '5511999666555',
                'user_id' => $support->id,
                'cliente_id' => $cliente1->id,
                'descricao' => 'Token de autenticação expira muito rápido.',
                'status' => 'resolvido',
                'priority' => 'média',
            ],
            [
                'title' => 'Melhoria na interface',
                'nome_cliente' => 'Ricardo Nunes',
                'whatsapp_numero' => '5511999555444',
                'user_id' => $admin->id,
                'cliente_id' => null,
                'descricao' => 'Sugestão de melhorias na interface do usuário.',
                'status' => 'resolvido',
                'priority' => 'baixa',
            ],
            [
                'title' => 'Problema com exportação',
                'nome_cliente' => 'Beatriz Martins',
                'whatsapp_numero' => '5511999444333',
                'user_id' => $assistant->id,
                'cliente_id' => $cliente2->id,
                'descricao' => 'Não consigo exportar os dados em CSV.',
                'status' => 'resolvido',
                'priority' => 'média',
            ],
            [
                'title' => 'Dúvida sobre integração',
                'nome_cliente' => 'Thiago Barbosa',
                'whatsapp_numero' => '5511999333222',
                'user_id' => $support->id,
                'cliente_id' => null,
                'descricao' => 'Como integrar o sistema com API externa?',
                'status' => 'resolvido',
                'priority' => 'baixa',
            ],

            // Tickets finalizados
            [
                'title' => 'Bug no formulário',
                'nome_cliente' => 'Amanda Dias',
                'whatsapp_numero' => '5511999222111',
                'user_id' => $support->id,
                'cliente_id' => $cliente1->id,
                'descricao' => 'O formulário de cadastro não valida campos obrigatórios.',
                'status' => 'finalizado',
                'priority' => 'alta',
            ],
            [
                'title' => 'Solicitação de documentação',
                'nome_cliente' => 'Gabriel Moreira',
                'whatsapp_numero' => '5511999111000',
                'user_id' => $admin->id,
                'cliente_id' => null,
                'descricao' => 'Preciso da documentação completa da API.',
                'status' => 'finalizado',
                'priority' => 'média',
            ],
            [
                'title' => 'Problema com performance',
                'nome_cliente' => 'Larissa Araújo',
                'whatsapp_numero' => '5511999000999',
                'user_id' => $support->id,
                'cliente_id' => $cliente2->id,
                'descricao' => 'O sistema está muito lento ao carregar muitos tickets.',
                'status' => 'finalizado',
                'priority' => 'alta',
            ],
            [
                'title' => 'Dúvida sobre backup',
                'nome_cliente' => 'Felipe Cardoso',
                'whatsapp_numero' => '5511998999888',
                'user_id' => $admin->id,
                'cliente_id' => null,
                'descricao' => 'Como funciona o sistema de backup dos dados?',
                'status' => 'finalizado',
                'priority' => 'baixa',
            ],
            [
                'title' => 'Problema com notificações',
                'nome_cliente' => 'Isabela Ramos',
                'whatsapp_numero' => '5511998888777',
                'user_id' => $assistant->id,
                'cliente_id' => $cliente1->id,
                'descricao' => 'Não estou recebendo notificações de novos chamados.',
                'status' => 'finalizado',
                'priority' => 'média',
            ],
        ];

        foreach ($tickets as $ticketData) {
            Ticket::create($ticketData);
        }

        $this->command->info('✅ 5 usuários criados com sucesso!');
        $this->command->info('✅ 20 tickets criados com sucesso!');
        $this->command->info('');
        $this->command->info('📧 Credenciais dos usuários (senha: password123):');
        $this->command->info('   - Admin: admin@teste.com');
        $this->command->info('   - Suporte: suporte@teste.com');
        $this->command->info('   - Assistente: assistente@teste.com');
        $this->command->info('   - Cliente 1: cliente1@teste.com');
        $this->command->info('   - Cliente 2: cliente2@teste.com');
    }
}

