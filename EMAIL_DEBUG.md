# Debug de Email - Sistema de Recuperação de Senha

## Comandos de Debug Criados

### 1. **Verificar Configurações de Email**
```bash
php artisan mail:check-config
```
Este comando mostra:
- ✅ Variáveis de ambiente
- ✅ Configurações do Laravel
- ✅ Teste de conexão SMTP
- ✅ Verificação do template
- ✅ Sugestões de troubleshooting

### 2. **Testar Envio de Email**
```bash
php artisan test:email admin@example.com
```
Este comando:
- ✅ Mostra configurações atuais
- ✅ Envia email de teste
- ✅ Registra logs detalhados
- ✅ Mostra informações de debug

## Logs Implementados

### **No PasswordResetController:**
- ✅ **Log antes do envio** - Configurações e dados
- ✅ **Log de sucesso** - Confirmação de envio
- ✅ **Log de erro** - Detalhes do erro e stack trace
- ✅ **Informações de debug** - Configurações completas

### **Verificar Logs:**
```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Ver logs específicos de email
grep "email" storage/logs/laravel.log

# Ver logs de erro
grep "ERROR" storage/logs/laravel.log
```

## Troubleshooting Passo a Passo

### **1. Verificar Configurações**
```bash
# Verificar se as variáveis estão definidas
php artisan mail:check-config
```

### **2. Limpar Cache**
```bash
# Limpar cache de configuração
php artisan config:clear

# Limpar cache de views
php artisan view:clear
```

### **3. Testar Conexão SMTP**
```bash
# Testar conectividade
telnet smtp.titan.email 465

# Ou usar netcat
nc -zv smtp.titan.email 465
```

### **4. Verificar Credenciais**
```bash
# Verificar se as credenciais estão corretas
php artisan tinker
>>> config('mail.mailers.smtp.username')
>>> config('mail.mailers.smtp.password')
```

### **5. Testar Envio Manual**
```bash
# Testar envio com logs
php artisan test:email admin@example.com
```

## Configurações Necessárias no .env

```env
# Configurações de Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=465
MAIL_USERNAME=site@revistaimagemindustrial.com
MAIL_PASSWORD=J~rN]4g%:8W-,)}
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=site@revistaimagemindustrial.com
MAIL_FROM_NAME="Revista-Imagem-Industrial"

# URL do Frontend
FRONTEND_URL=http://localhost:3000
```

## Problemas Comuns e Soluções

### **1. Email não enviado mas retorna sucesso**
**Possíveis causas:**
- Configurações SMTP incorretas
- Credenciais inválidas
- Firewall bloqueando porta 465
- Servidor SMTP indisponível

**Soluções:**
```bash
# Verificar configurações
php artisan mail:check-config

# Testar conectividade
telnet smtp.titan.email 465

# Verificar logs
tail -f storage/logs/laravel.log
```

### **2. Erro de autenticação SMTP**
**Possíveis causas:**
- Username/password incorretos
- Autenticação não habilitada
- Conta bloqueada

**Soluções:**
- Verificar credenciais no painel do Titan
- Testar login manual no servidor SMTP
- Verificar se a conta não está bloqueada

### **3. Erro de conexão**
**Possíveis causas:**
- Porta 465 bloqueada
- Firewall corporativo
- DNS não resolve

**Soluções:**
```bash
# Testar conectividade
ping smtp.titan.email
telnet smtp.titan.email 465

# Verificar DNS
nslookup smtp.titan.email
```

### **4. Template não encontrado**
**Possíveis causas:**
- Arquivo não existe
- Permissões incorretas
- Cache de views

**Soluções:**
```bash
# Verificar se existe
ls -la resources/views/emails/password-reset.blade.php

# Limpar cache
php artisan view:clear

# Verificar permissões
chmod 644 resources/views/emails/password-reset.blade.php
```

## Exemplo de Logs Esperados

### **Log de Sucesso:**
```
[2025-10-21 13:30:00] local.INFO: Tentando enviar email de recuperação {"email":"admin@example.com","reset_link":"http://localhost:3000/reset-password?token=abc123&email=admin%40example.com","mail_config":{"driver":"smtp","host":"smtp.titan.email","port":"465","encryption":"ssl","username":"site@revistaimagemindustrial.com","from_address":"site@revistaimagemindustrial.com","from_name":"Revista-Imagem-Industrial"}}

[2025-10-21 13:30:01] local.INFO: Email enviado com sucesso {"email":"admin@example.com","reset_link":"http://localhost:3000/reset-password?token=abc123&email=admin%40example.com"}
```

### **Log de Erro:**
```
[2025-10-21 13:30:00] local.ERROR: Erro ao enviar email de recuperação {"email":"admin@example.com","error":"Connection could not be established with host smtp.titan.email :stream_socket_client(): unable to connect to smtp.titan.email:465 (Connection timed out)","trace":"#0 /vendor/swiftmailer/swiftmailer/lib/classes/Swift/Transport/StreamBuffer.php(269): stream_socket_client()","mail_config":{"driver":"smtp","host":"smtp.titan.email","port":"465","encryption":"ssl","username":"site@revistaimagemindustrial.com"}}
```

## Testando a API

### **1. Solicitar Reset (com logs)**
```bash
curl -X POST http://localhost:8000/api/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com"}' \
  -v
```

### **2. Verificar Resposta**
```json
{
    "message": "Link de recuperação enviado para seu email.",
    "expires_in": "1 hora",
    "debug": {
        "email_sent_to": "admin@example.com",
        "reset_link": "http://localhost:3000/reset-password?token=abc123&email=admin%40example.com"
    }
}
```

### **3. Em Caso de Erro**
```json
{
    "message": "Link de recuperação gerado.",
    "reset_link": "http://localhost:3000/reset-password?token=abc123&email=admin%40example.com",
    "token": "abc123",
    "expires_in": "1 hora",
    "note": "Configure o email em produção",
    "error": "Connection could not be established...",
    "debug_info": {
        "email_sent_to": "admin@example.com",
        "reset_link": "http://localhost:3000/reset-password?token=abc123&email=admin%40example.com",
        "mail_config": {...}
    }
}
```

## Comandos Úteis para Debug

```bash
# Verificar configurações
php artisan mail:check-config

# Testar envio
php artisan test:email admin@example.com

# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Limpar cache
php artisan config:clear
php artisan view:clear

# Verificar template
ls -la resources/views/emails/password-reset.blade.php

# Testar conectividade
telnet smtp.titan.email 465
```

Agora você tem logs detalhados para identificar exatamente onde está o problema no envio de email!
