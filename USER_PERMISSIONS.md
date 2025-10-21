# Permissões de Usuários - Sistema de Update

## Regras de Permissão para Update de Usuários

### 🔧 **Admin (Administrator)**
- ✅ **Pode editar qualquer usuário** (incluindo outros admins)
- ✅ **Pode alterar role** de qualquer usuário
- ✅ **Pode alterar nome e email** de qualquer usuário
- ✅ **Acesso total** ao gerenciamento de usuários

### 🛠️ **Support (Suporte)**
- ✅ **Pode editar apenas seus próprios dados**
- ❌ **NÃO pode editar outros usuários**
- ❌ **NÃO pode alterar role** (nem o próprio)
- ✅ **Pode alterar nome e email** apenas do próprio perfil

### 👥 **Assistant (Assistente)**
- ✅ **Pode editar apenas seus próprios dados**
- ❌ **NÃO pode editar outros usuários**
- ❌ **NÃO pode alterar role** (nem o próprio)
- ✅ **Pode alterar nome e email** apenas do próprio perfil

## Implementação Técnica

### Controller UserController::update()
```php
public function update(Request $request, User $user)
{
    $currentUser = $request->user();
    
    // Verificar se o usuário pode editar este usuário
    if (!$currentUser->canManageUsers() && $currentUser->id !== $user->id) {
        return response()->json(['message' => 'Acesso negado. Você só pode editar seus próprios dados.'], 403);
    }
    
    // Se não é admin, não pode alterar role
    $validationRules = [
        'name' => 'sometimes|string|max:100',
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
    ];
    
    // Apenas admin pode alterar role
    if ($currentUser->canManageUsers()) {
        $validationRules['role'] = 'sometimes|in:admin,support,assistant';
    }
    
    // ... validação e update
}
```

### Rotas Configuradas
```php
// Apenas Admin pode listar, criar, visualizar e deletar usuários
Route::get('users', [UserController::class, 'index'])
    ->middleware('role:admin');
Route::post('users', [UserController::class, 'store'])
    ->middleware('role:admin');
Route::get('users/{user}', [UserController::class, 'show'])
    ->middleware('role:admin');
Route::delete('users/{user}', [UserController::class, 'destroy'])
    ->middleware('role:admin');

// Todos podem atualizar usuários (com verificação no controller)
Route::put('users/{user}', [UserController::class, 'update']);
```

## Exemplos de Uso da API

### 1. Admin editando outro usuário
```bash
PUT /api/users/2
Authorization: Bearer {admin_token}
{
    "name": "Novo Nome",
    "email": "novo@email.com",
    "role": "support"
}
```
**Resposta:** ✅ Sucesso - Admin pode alterar qualquer usuário

### 2. Support editando próprio perfil
```bash
PUT /api/users/2
Authorization: Bearer {support_token}
{
    "name": "Meu Nome Atualizado",
    "email": "meu@email.com"
}
```
**Resposta:** ✅ Sucesso - Support pode editar seus próprios dados

### 3. Support tentando editar outro usuário
```bash
PUT /api/users/1
Authorization: Bearer {support_token}
{
    "name": "Tentativa de Edição"
}
```
**Resposta:** ❌ 403 - "Acesso negado. Você só pode editar seus próprios dados."

### 4. Assistant tentando alterar role
```bash
PUT /api/users/3
Authorization: Bearer {assistant_token}
{
    "name": "Meu Nome",
    "role": "admin"
}
```
**Resposta:** ✅ Sucesso - Mas o campo `role` é ignorado (não validado)

### 5. Assistant tentando editar outro usuário
```bash
PUT /api/users/1
Authorization: Bearer {assistant_token}
{
    "name": "Tentativa"
}
```
**Resposta:** ❌ 403 - "Acesso negado. Você só pode editar seus próprios dados."

## Perfil Próprio (Rota /me)

### Todos os usuários podem editar seu próprio perfil
```bash
PUT /api/me
Authorization: Bearer {qualquer_token}
{
    "name": "Meu Nome",
    "email": "meu@email.com"
}
```
**Resposta:** ✅ Sucesso - Todos podem editar seus próprios dados

## Validações Implementadas

### Campos que todos podem alterar (em seus próprios dados):
- ✅ `name` - Nome do usuário
- ✅ `email` - Email do usuário

### Campos que apenas Admin pode alterar:
- ✅ `role` - Função do usuário (admin, support, assistant)

### Verificações de Segurança:
1. **Autenticação obrigatória** - Token Sanctum
2. **Verificação de propriedade** - Só pode editar próprios dados (exceto admin)
3. **Validação condicional** - Role só é validado se for admin
4. **Mensagens em português** - Erros claros e informativos

## Códigos de Resposta

| Código | Significado | Exemplo |
|--------|-------------|---------|
| 200 | Sucesso | Usuário atualizado |
| 401 | Não autenticado | Token inválido |
| 403 | Acesso negado | Tentativa de editar outro usuário |
| 422 | Dados inválidos | Email já existe, nome muito longo |

## Testando as Permissões

### Usuários de Teste:
```bash
# Admin
POST /api/login
{
    "email": "admin@example.com",
    "password": "password123"
}

# Support  
POST /api/login
{
    "email": "support@example.com", 
    "password": "password123"
}

# Assistant
POST /api/login
{
    "email": "assistant@example.com",
    "password": "password123"
}
```

### Cenários de Teste:
1. ✅ Admin edita qualquer usuário
2. ✅ Support edita apenas seus dados
3. ❌ Support não pode editar outros
4. ✅ Assistant edita apenas seus dados  
5. ❌ Assistant não pode editar outros
6. ❌ Ninguém pode alterar role exceto admin
