<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
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
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
        <h1>🔐 Recuperação de Senha</h1>
    </div>

    <div class="content">
        <h2>Olá, {{ $user->name }}!</h2>

        <p>Recebemos uma solicitação para redefinir a senha da sua conta em nosso sistema.</p>

        <p>Para criar uma nova senha, clique no botão abaixo:</p>

        <div style="text-align: center;">
            <a href="{{ $resetLink }}" class="button">
                🔑 Redefinir Minha Senha
            </a>
        </div>

        <div class="warning">
            <strong>⚠️ Importante:</strong>
            <ul>
                <li>Este link é válido por <strong>1 hora</strong></li>
                <li>Se você não solicitou esta alteração, ignore este email</li>
                <li>Não compartilhe este link com ninguém</li>
            </ul>
        </div>

        <p>Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</p>
        <p style="word-break: break-all; background-color: #e9ecef; padding: 10px; border-radius: 3px;">
            {{ $resetLink }}
        </p>

        <p>Se você não solicitou esta alteração, pode ignorar este email com segurança.</p>

        <p>Atenciosamente,<br>
        <strong>Equipe do Sistema de Chamados</strong></p>
    </div>

    <div class="footer">
        <p>Este é um email automático, não responda a esta mensagem.</p>
        <p>© {{ date('Y') }} Sistema de Chamados. Todos os direitos reservados.</p>
    </div>
</body>
</html>
