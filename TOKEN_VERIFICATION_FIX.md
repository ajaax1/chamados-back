# Correção do Erro de Verificação de Token

## Problema Identificado

**Erro:** `Call to a member function addHour() on string`

**Causa:** O campo `created_at` estava sendo tratado como string em vez de objeto Carbon, causando erro ao tentar chamar `addHour()`.

## Solução Implementada

### **1. Correção no PasswordResetController**

**Antes (com erro):**
```php
return response()->json([
    'message' => 'Token válido',
    'email' => $email,
    'expires_at' => $passwordReset->created_at->addHour()->toISOString()
]);
```

**Depois (corrigido):**
```php
$createdAt = \Carbon\Carbon::parse($passwordReset->created_at);
$expiresAt = $createdAt->addHour()->toISOString();

return response()->json([
    'message' => 'Token válido',
    'email' => $email,
    'expires_at' => $expiresAt
]);
```

### **2. Logs Detalhados Adicionados**

```php
\Log::info('Verificando token de reset', [
    'token' => $token,
    'email' => $email,
    'query_params' => $request->query(),
    'input_data' => $request->input()
]);

\Log::info('Token encontrado no banco', [
    'email' => $email,
    'created_at' => $passwordReset->created_at,
    'created_at_type' => gettype($passwordReset->created_at)
]);

\Log::info('Verificando expiração do token', [
    'created_at' => $createdAt->toISOString(),
    'now' => now()->toISOString(),
    'hours_diff' => $hoursDiff
]);
```

## Comandos de Teste Criados

### **1. Testar Verificação de Token**
```bash
php artisan test:token-verification admin@example.com
```

Este comando:
- ✅ Cria um token de teste
- ✅ Testa a API de verificação
- ✅ Testa a lógica manual
- ✅ Mostra tipos de dados
- ✅ Limpa o token de teste

### **2. Verificar Configurações de Email**
```bash
php artisan mail:check-config
```

### **3. Testar Envio de Email**
```bash
php artisan test:email admin@example.com
```

## Como Testar a Correção

### **1. Teste via Comando**
```bash
php artisan test:token-verification admin@example.com
```

### **2. Teste via API**
```bash
# Primeiro, solicitar reset
curl -X POST http://localhost:8000/api/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com"}'

# Depois, verificar token (usar o token retornado)
curl "http://localhost:8000/api/password/verify-token?token=TOKEN_AQUI&email=admin@example.com"
```

### **3. Verificar Logs**
```bash
tail -f storage/logs/laravel.log
```

## Exemplo de Resposta Corrigida

### **Token Válido:**
```json
{
    "message": "Token válido",
    "email": "admin@example.com",
    "expires_at": "2025-10-21T14:30:00.000000Z"
}
```

### **Token Inválido:**
```json
{
    "message": "Token inválido ou expirado"
}
```

### **Token Expirado:**
```json
{
    "message": "Token expirado"
}
```

## Logs Esperados

### **Log de Sucesso:**
```
[INFO] Verificando token de reset {"token":"abc123","email":"admin@example.com"}
[INFO] Token encontrado no banco {"email":"admin@example.com","created_at":"2025-10-21T13:30:00.000000Z","created_at_type":"string"}
[INFO] Verificando expiração do token {"created_at":"2025-10-21T13:30:00.000000Z","now":"2025-10-21T13:45:00.000000Z","hours_diff":0}
[INFO] Token válido {"email":"admin@example.com","expires_at":"2025-10-21T14:30:00.000000Z"}
```

### **Log de Erro:**
```
[WARNING] Token não encontrado no banco {"email":"admin@example.com","token":"token_invalido"}
```

## Troubleshooting

### **1. Se ainda der erro:**
```bash
# Verificar logs
tail -f storage/logs/laravel.log

# Testar comando
php artisan test:token-verification admin@example.com

# Limpar cache
php artisan config:clear
```

### **2. Se o token não for encontrado:**
- Verificar se o email está correto
- Verificar se o token foi criado corretamente
- Verificar se não expirou (1 hora)

### **3. Se der erro de Carbon:**
- Verificar se o Carbon está instalado
- Verificar se o campo created_at está sendo salvo corretamente

## Código Final do Método verifyToken

```php
public function verifyToken(Request $request)
{
    // Aceitar tanto query parameters (GET) quanto body (POST)
    $token = $request->query('token') ?? $request->input('token');
    $email = $request->query('email') ?? $request->input('email');

    \Log::info('Verificando token de reset', [
        'token' => $token,
        'email' => $email,
        'query_params' => $request->query(),
        'input_data' => $request->input()
    ]);

    if (!$token || !$email) {
        \Log::warning('Token ou email não fornecidos', [
            'token_provided' => !empty($token),
            'email_provided' => !empty($email)
        ]);
        return response()->json(['message' => 'Token e email são obrigatórios'], 400);
    }

    $passwordReset = PasswordReset::where('email', $email)
        ->where('token', $token)
        ->first();

    if (!$passwordReset) {
        \Log::warning('Token não encontrado no banco', [
            'email' => $email,
            'token' => $token
        ]);
        return response()->json(['message' => 'Token inválido ou expirado'], 400);
    }

    \Log::info('Token encontrado no banco', [
        'email' => $email,
        'created_at' => $passwordReset->created_at,
        'created_at_type' => gettype($passwordReset->created_at)
    ]);

    // Verificar se o token não expirou (1 hora)
    $createdAt = \Carbon\Carbon::parse($passwordReset->created_at);
    $hoursDiff = now()->diffInHours($createdAt);
    
    \Log::info('Verificando expiração do token', [
        'created_at' => $createdAt->toISOString(),
        'now' => now()->toISOString(),
        'hours_diff' => $hoursDiff
    ]);

    if ($hoursDiff > 1) {
        \Log::info('Token expirado, removendo do banco', [
            'email' => $email,
            'hours_diff' => $hoursDiff
        ]);
        $passwordReset->delete();
        return response()->json(['message' => 'Token expirado'], 400);
    }

    $expiresAt = $createdAt->addHour()->toISOString();
    
    \Log::info('Token válido', [
        'email' => $email,
        'expires_at' => $expiresAt
    ]);

    return response()->json([
        'message' => 'Token válido',
        'email' => $email,
        'expires_at' => $expiresAt
    ]);
}
```

Agora o erro `Call to a member function addHour() on string` foi corrigido e você tem logs detalhados para debug!




