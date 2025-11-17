<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Chamado Atribuído</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #218838;
        }
        .ticket-info {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .ticket-info strong {
            color: #007bff;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Novo Chamado Atribuído</h1>
    </div>

    <div class="content">
        <h2>Olá, {{ $user->name }}!</h2>

        <p>Um novo chamado foi atribuído a você como <strong>{{ $role }}</strong> no sistema de chamados.</p>

        <div class="ticket-info">
            <p><strong>Título:</strong> {{ $ticket->title }}</p>
            <p><strong>Número do Chamado:</strong> #{{ $ticket->id }}</p>
            <p><strong>Cliente:</strong> {{ $ticket->nome_cliente }}</p>
            <p><strong>Status:</strong> {{ ucfirst($ticket->status) }}</p>
            <p><strong>Prioridade:</strong> {{ ucfirst($ticket->priority) }}</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ $ticketUrl }}" class="button">
                📝 Ver Chamado
            </a>
        </div>

        <p>Para visualizar os detalhes completos do chamado e acompanhar seu andamento, clique no botão acima ou acesse o sistema de chamados.</p>

        <p>Atenciosamente,<br>
        <strong>Equipe do Sistema de Chamados</strong></p>
    </div>

    <div class="footer">
        <p>Este é um email automático, não responda a esta mensagem.</p>
        <p>© {{ date('Y') }} Sistema de Chamados. Todos os direitos reservados.</p>
    </div>
</body>
</html>

