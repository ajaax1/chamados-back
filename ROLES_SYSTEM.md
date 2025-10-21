# Sistema de Roles e Permissões

## Tipos de Usuários

### 🔧 **Admin** (Administrator)
- **Descrição:** Gerencia todos os recursos do sistema
- **Permissões:**
  - ✅ Gerenciar usuários (criar, editar, excluir)
  - ✅ Ver todos os tickets
  - ✅ Editar qualquer ticket
  - ✅ Excluir qualquer ticket
  - ✅ Criar tickets
  - ✅ Responder tickets
  - ✅ Ver estatísticas

### 🛠️ **Support** (Suporte)
- **Descrição:** Cria, responde e exclui chamados
- **Permissões:**
  - ❌ Gerenciar usuários
  - ✅ Ver todos os tickets
  - ✅ Editar qualquer ticket
  - ✅ Excluir qualquer ticket
  - ✅ Criar tickets
  - ✅ Responder tickets
  - ✅ Ver estatísticas

### 👥 **Assistant** (Assistente)
- **Descrição:** Pode apenas criar e responder chamados
- **Permissões:**
  - ❌ Gerenciar usuários
  - ❌ Ver todos os tickets (apenas os próprios)
  - ❌ Editar tickets de outros usuários
  - ❌ Excluir tickets de outros usuários
  - ✅ Criar tickets
  - ✅ Responder tickets
  - ❌ Ver estatísticas globais

## Estrutura de Permissões

### Gerenciamento de Usuários
```php
// Apenas Admin pode gerenciar usuários
Route::apiResource('users', UserController::class)
    ->middleware('role:admin');
```

### Tickets
```php
// Todos podem criar tickets
Route::post('tickets', [TicketController::class, 'store']);

// Apenas Support e Admin podem editar
Route::put('tickets/{ticket}', [TicketController::class, 'update'])
    ->middleware('role:support');

// Apenas Support e Admin podem excluir
Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])
    ->middleware('role:support');
```

## Middleware de Roles

### CheckRole Middleware
```php
// Uso nas rotas
Route::get('/admin-only', [Controller::class, 'method'])
    ->middleware('role:admin');

Route::get('/support-or-admin', [Controller::class, 'method'])
    ->middleware('role:support');
```

### Verificação de Permissões no Controller
```php
public function index(Request $request)
{
    $user = $request->user();
    
    // Assistants só veem seus próprios tickets
    if (!$user->canViewAllTickets()) {
        $query->where('user_id', $user->id);
    }
    
    return $query->paginate(10);
}
```

## Métodos de Verificação no Model User

```php
// Verificação de roles
$user->isAdmin()        // true se for admin
$user->isSupport()      // true se for support
$user->isAssistant()    // true se for assistant

// Verificação de permissões
$user->canManageUsers()     // Apenas admin
$user->canDeleteTickets()    // Admin e support
$user->canEditTickets()      // Admin e support
$user->canViewAllTickets()   // Admin e support
$user->canCreateTickets()    // Todos
$user->canRespondTickets()  // Todos
```

## Usuários de Teste

Após executar o seeder, você terá os seguintes usuários:

| Role | Email | Password | Permissões |
|------|-------|----------|------------|
| **Admin** | admin@example.com | password123 | Todas as permissões |
| **Support** | support@example.com | password123 | Gerenciar tickets |
| **Assistant** | assistant@example.com | password123 | Apenas criar/responder |

## Exemplos de Uso da API

### Login como Admin
```bash
POST /api/login
{
    "email": "admin@example.com",
    "password": "password123"
}
```

### Gerenciar Usuários (apenas Admin)
```bash
# Listar usuários
GET /api/users
Authorization: Bearer {admin_token}

# Criar usuário
POST /api/users
Authorization: Bearer {admin_token}
{
    "name": "Novo Usuário",
    "email": "novo@example.com",
    "password": "senha123",
    "role": "assistant"
}
```

### Gerenciar Tickets

#### Como Admin/Support:
```bash
# Ver todos os tickets
GET /api/tickets-filtro
Authorization: Bearer {token}

# Editar qualquer ticket
PUT /api/tickets/{id}
Authorization: Bearer {token}

# Excluir qualquer ticket
DELETE /api/tickets/{id}
Authorization: Bearer {token}
```

#### Como Assistant:
```bash
# Ver apenas seus próprios tickets
GET /api/tickets-filtro
Authorization: Bearer {assistant_token}

# Tentar editar ticket de outro usuário (retorna 403)
PUT /api/tickets/{id}
Authorization: Bearer {assistant_token}
# Response: {"message": "Access denied. You can only edit your own tickets."}
```

## Códigos de Resposta

| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 401 | Não autenticado |
| 403 | Acesso negado (sem permissão) |
| 404 | Recurso não encontrado |

## Migração e Seeder

### Executar Migration
```bash
php artisan migrate
```

### Executar Seeder
```bash
php artisan db:seed --class=UserRoleSeeder
```

## Estrutura do Banco

### Tabela users
```sql
- id (bigint)
- name (string)
- email (string, unique)
- password (string)
- role (enum: 'admin', 'support', 'assistant')
- created_at (timestamp)
- updated_at (timestamp)
```

## Segurança

1. **Middleware de Autenticação:** Todas as rotas protegidas requerem token Sanctum
2. **Middleware de Roles:** Verificação de permissões baseada em roles
3. **Validação no Controller:** Verificação adicional de permissões
4. **Filtros de Dados:** Usuários só veem dados que têm permissão

## Exemplo de Frontend

```javascript
// Verificar role do usuário
const user = await fetch('/api/me', {
    headers: { 'Authorization': `Bearer ${token}` }
}).then(r => r.json());

if (user.role === 'admin') {
    // Mostrar botões de gerenciamento de usuários
    showUserManagement();
}

if (user.role === 'assistant') {
    // Ocultar botões de editar/excluir tickets de outros
    hideTicketActions();
}
```
