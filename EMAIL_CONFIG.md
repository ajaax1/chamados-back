# Configuração de Email - Recuperação de Senha

## Configurações do Email

As seguintes configurações foram aplicadas para o sistema de recuperação de senha:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=465
MAIL_USERNAME=site@revistaimagemindustrial.com
MAIL_PASSWORD=J~rN]4g%:8W-,)}
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=site@revistaimagemindustrial.com
MAIL_FROM_NAME="Revista-Imagem-Industrial"
```

## Rotas de Recuperação de Senha

### 1. **Solicitar Reset de Senha**
```bash
POST /api/password/forgot
Content-Type: application/json

{
    "email": "usuario@exemplo.com"
}
```

**Resposta de Sucesso:**
```json
{
    "message": "Link de recuperação enviado para seu email.",
    "expires_in": "1 hora"
}
```

**Resposta de Erro:**
```json
{
    "message": "Este email não está cadastrado em nosso sistema.",
    "errors": {
        "email": ["Este email não está cadastrado em nosso sistema."]
    }
}
```

### 2. **Verificar Token**
```bash
POST /api/password/verify-token
Content-Type: application/json

{
    "token": "token_aqui",
    "email": "usuario@exemplo.com"
}
```

**Resposta de Sucesso:**
```json
{
    "message": "Token válido",
    "email": "usuario@exemplo.com"
}
```

### 3. **Resetar Senha**
```bash
POST /api/password/reset
Content-Type: application/json

{
    "token": "token_aqui",
    "email": "usuario@exemplo.com",
    "password": "nova_senha123",
    "password_confirmation": "nova_senha123"
}
```

**Resposta de Sucesso:**
```json
{
    "message": "Senha alterada com sucesso!"
}
```

### 4. **Alterar Senha (Usuário Logado)**
```bash
POST /api/password/change
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "senha_atual",
    "password": "nova_senha123",
    "password_confirmation": "nova_senha123"
}
```

## Template de Email

O sistema utiliza um template HTML responsivo localizado em:
`resources/views/emails/password-reset.blade.php`

### Características do Template:
- ✅ **Design responsivo** para mobile e desktop
- ✅ **Botão de ação** destacado para redefinir senha
- ✅ **Avisos de segurança** sobre o link
- ✅ **Link alternativo** caso o botão não funcione
- ✅ **Branding** da Revista Imagem Industrial

## Configuração do .env

Para configurar o email, adicione as seguintes linhas ao seu arquivo `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=465
MAIL_USERNAME=site@revistaimagemindustrial.com
MAIL_PASSWORD=J~rN]4g%:8W-,)}
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=site@revistaimagemindustrial.com
MAIL_FROM_NAME="Revista-Imagem-Industrial"
```

## Testando o Sistema

### 1. **Teste de Solicitação de Reset**
```bash
curl -X POST http://localhost:8000/api/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com"}'
```

### 2. **Verificar Logs de Email**
```bash
# Em desenvolvimento, verifique os logs
tail -f storage/logs/laravel.log
```

### 3. **Teste com Mailtrap (Desenvolvimento)**
Para desenvolvimento, você pode usar o Mailtrap:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

## Segurança Implementada

### ✅ **Token Único**
- Token de 64 caracteres gerado aleatoriamente
- Tokens antigos são removidos ao gerar novos

### ✅ **Expiração**
- Tokens expiram em 1 hora
- Verificação automática de expiração

### ✅ **Validações**
- Email deve existir no sistema
- Senha deve ter pelo menos 6 caracteres
- Confirmação de senha obrigatória

### ✅ **Limpeza**
- Tokens são removidos após uso
- Tokens expirados são automaticamente limpos

## Exemplo de Frontend

### JavaScript - Solicitar Reset
```javascript
async function requestPasswordReset(email) {
    try {
        const response = await fetch('/api/password/forgot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Link de recuperação enviado para seu email!');
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao solicitar recuperação de senha');
    }
}
```

### JavaScript - Resetar Senha
```javascript
async function resetPassword(token, email, password, passwordConfirmation) {
    try {
        const response = await fetch('/api/password/reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                token,
                email,
                password,
                password_confirmation: passwordConfirmation
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Senha alterada com sucesso!');
            window.location.href = '/login';
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao alterar senha');
    }
}
```

## Troubleshooting

### Problemas Comuns:

1. **Email não enviado**
   - Verifique as configurações SMTP
   - Confirme credenciais do servidor
   - Verifique logs do Laravel

2. **Token inválido**
   - Verifique se o token não expirou (1 hora)
   - Confirme se o email está correto
   - Verifique se o token foi usado

3. **Erro de conexão SMTP**
   - Verifique se a porta 465 está liberada
   - Confirme se o SSL está configurado
   - Teste as credenciais manualmente

### Comandos Úteis:

```bash
# Limpar cache de configuração
php artisan config:clear

# Testar configuração de email
php artisan tinker
>>> Mail::raw('Teste', function($msg) { $msg->to('test@example.com')->subject('Teste'); });

# Ver logs de email
tail -f storage/logs/laravel.log
```
