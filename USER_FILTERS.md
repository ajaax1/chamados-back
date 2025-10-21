# Filtros de Usuários - API Documentation

## Endpoint de Listagem com Filtros

### **GET /api/users**

Lista usuários com filtros opcionais e paginação.

## Parâmetros de Query

| Parâmetro | Tipo | Obrigatório | Descrição | Exemplo |
|-----------|------|-------------|-----------|---------|
| `search` | string | Não | Busca por nome do usuário | `?search=João` |
| `role` | string | Não | Filtra por role específico | `?role=admin` |
| `page` | integer | Não | Número da página | `?page=2` |

## Exemplos de Uso

### 1. Listar todos os usuários
```bash
GET /api/users
Authorization: Bearer {admin_token}
```

**Resposta:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Administrator",
            "email": "admin@example.com",
            "role": "admin",
            "created_at": "2025-10-21T13:06:05.000000Z",
            "updated_at": "2025-10-21T13:06:05.000000Z"
        },
        {
            "id": 2,
            "name": "Support Agent",
            "email": "support@example.com",
            "role": "support",
            "created_at": "2025-10-21T13:06:05.000000Z",
            "updated_at": "2025-10-21T13:06:05.000000Z"
        }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 3,
    "last_page": 1
}
```

### 2. Buscar usuários por nome
```bash
GET /api/users?search=Admin
Authorization: Bearer {admin_token}
```

**Resposta:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Administrator",
            "email": "admin@example.com",
            "role": "admin"
        }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
}
```

### 3. Filtrar por role
```bash
GET /api/users?role=support
Authorization: Bearer {admin_token}
```

**Resposta:**
```json
{
    "data": [
        {
            "id": 2,
            "name": "Support Agent",
            "email": "support@example.com",
            "role": "support"
        }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
}
```

### 4. Combinar filtros
```bash
GET /api/users?search=Agent&role=support
Authorization: Bearer {admin_token}
```

**Resposta:**
```json
{
    "data": [
        {
            "id": 2,
            "name": "Support Agent",
            "email": "support@example.com",
            "role": "support"
        }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
}
```

### 5. Paginação
```bash
GET /api/users?page=2
Authorization: Bearer {admin_token}
```

## Valores Válidos para Role

| Role | Descrição |
|------|-----------|
| `admin` | Administrador |
| `support` | Suporte |
| `assistant` | Assistente |

## Implementação Técnica

### Controller UserController::index()
```php
public function index(Request $request)
{
    $query = User::query();

    // Filtro por nome
    if ($search = $request->query('search')) {
        $query->where('name', 'like', "%{$search}%");
    }

    // Filtro por role
    if ($role = $request->query('role')) {
        $query->where('role', $role);
    }

    // Ordenação
    $query->orderBy('name', 'asc');

    return response()->json($query->paginate(15));
}
```

## Características dos Filtros

### 🔍 **Busca por Nome**
- **Case-insensitive** (não diferencia maiúsculas/minúsculas)
- **Busca parcial** (encontra "João" em "João Silva")
- **Usa LIKE** para busca flexível

### 🏷️ **Filtro por Role**
- **Busca exata** por role
- **Valores válidos:** admin, support, assistant
- **Case-sensitive** (deve ser exatamente como definido)

### 📄 **Paginação**
- **15 usuários por página** (padrão)
- **Ordenação alfabética** por nome
- **Metadados de paginação** incluídos na resposta

## Exemplos de Frontend

### JavaScript - Busca com filtros
```javascript
async function searchUsers(searchTerm = '', role = '') {
    const params = new URLSearchParams();
    
    if (searchTerm) params.append('search', searchTerm);
    if (role) params.append('role', role);
    
    const response = await fetch(`/api/users?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    return await response.json();
}

// Exemplos de uso
const allUsers = await searchUsers();
const adminUsers = await searchUsers('', 'admin');
const searchResults = await searchUsers('João');
```

### React - Componente de busca
```jsx
function UserSearch() {
    const [users, setUsers] = useState([]);
    const [search, setSearch] = useState('');
    const [role, setRole] = useState('');

    const searchUsers = async () => {
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (role) params.append('role', role);
        
        const response = await fetch(`/api/users?${params}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        const data = await response.json();
        setUsers(data.data);
    };

    return (
        <div>
            <input 
                type="text" 
                placeholder="Buscar por nome..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
            />
            <select value={role} onChange={(e) => setRole(e.target.value)}>
                <option value="">Todos os roles</option>
                <option value="admin">Admin</option>
                <option value="support">Support</option>
                <option value="assistant">Assistant</option>
            </select>
            <button onClick={searchUsers}>Buscar</button>
        </div>
    );
}
```

## Códigos de Resposta

| Código | Significado |
|--------|-------------|
| 200 | Sucesso - Lista de usuários retornada |
| 401 | Não autenticado - Token inválido |
| 403 | Acesso negado - Apenas admin pode listar usuários |

## Notas Importantes

1. **Apenas Admin** pode acessar esta rota
2. **Busca case-insensitive** para nomes
3. **Ordenação alfabética** por padrão
4. **Paginação automática** com 15 itens por página
5. **Filtros opcionais** - todos podem ser omitidos
