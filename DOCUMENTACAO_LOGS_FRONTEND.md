# 📋 Documentação: Sistema de Logs de Atividades - Frontend

## 📖 O que são os Logs?

Os logs de atividades são um registro completo de todas as ações realizadas no sistema. Eles capturam:
- **Quem** fez a ação (usuário)
- **O que** foi feito (ação: criado, atualizado, deletado, etc.)
- **Quando** foi feito (data/hora)
- **Onde** foi feito (em qual ticket, mensagem, etc.)
- **Como** mudou (valores antes e depois)

## 🎯 Para que servem?

1. **Auditoria**: Rastrear todas as ações para compliance e segurança
2. **Histórico**: Ver o que aconteceu em um ticket ou com um usuário
3. **Transparência**: Mostrar quem fez o quê e quando
4. **Resolução de problemas**: Entender o que causou uma situação
5. **Análise**: Ver padrões de uso e comportamento

---

## 🔐 Autenticação

Todas as rotas de logs requerem autenticação via token Sanctum:

```
Authorization: Bearer {seu_token}
```

---

## 📍 Rotas Disponíveis

### 1. Listar Todos os Logs

**Rota:** `GET /api/activity-logs`

**Descrição:** Retorna lista paginada de todos os logs de atividades.

**Parâmetros de Query (todos opcionais):**
- `user_id` - Filtrar por ID do usuário (pode escolher de quem ver os logs)
- `action` - Filtrar por ação (created, updated, deleted, viewed, assigned, status_changed)
- `model_type` - Filtrar por tipo (App\Models\Ticket, App\Models\TicketMessage, etc.)
- `model_id` - Filtrar por ID do model específico
- `period` - Filtrar por período: `day`, `week`, `month`, `year`, `all` (padrão: sem filtro)
- `from` - Data inicial (formato: YYYY-MM-DD)
- `to` - Data final (formato: YYYY-MM-DD)
- `per_page` - Itens por página (padrão: 50, máximo: 100)

**Permissões:**
- ✅ Admin: Vê todos os logs (pode filtrar por qualquer usuário)
- ✅ Support: Vê todos os logs (pode filtrar por qualquer usuário)
- ✅ Assistant: Vê todos os logs (pode filtrar por qualquer usuário)
- ✅ Cliente: Vê apenas seus próprios logs (não pode filtrar por outros usuários)

**Exemplos de Requisição:**

Ver logs de um usuário específico:
```
GET /api/activity-logs?user_id=5&period=month
```

Ver logs de um usuário com filtro de ação:
```
GET /api/activity-logs?user_id=5&action=created&period=week
```

Ver todos os logs do mês:
```
GET /api/activity-logs?period=month&per_page=25
```

Ver logs de um usuário em um ticket específico:
```
GET /api/activity-logs?user_id=5&model_type=App\Models\Ticket&model_id=123
```

**Estrutura da Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "action": "created",
      "model_type": "App\\Models\\Ticket",
      "model_id": 123,
      "old_values": null,
      "new_values": {
        "id": 123,
        "title": "Problema no sistema",
        "status": "aberto",
        "priority": "alta"
      },
      "description": "Ticket 'Problema no sistema' criado",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "metadata": {
        "cliente_id": 10,
        "assigned_to": 5,
        "status": "aberto",
        "priority": "alta"
      },
      "created_at": "2025-11-28T10:30:00.000000Z",
      "updated_at": "2025-11-28T10:30:00.000000Z",
      "user": {
        "id": 5,
        "name": "João Silva",
        "email": "joao@example.com",
        "role": "support"
      }
    }
  ],
  "current_page": 1,
  "per_page": 25,
  "total": 150,
  "last_page": 6
}
```

---

### 2. Ver Log Específico

**Rota:** `GET /api/activity-logs/{id}`

**Descrição:** Retorna detalhes completos de um log específico.

**Exemplo de Requisição:**
```
GET /api/activity-logs/1
```

**Estrutura da Resposta:**
```json
{
  "id": 1,
  "user_id": 5,
  "action": "status_changed",
  "model_type": "App\\Models\\Ticket",
  "model_id": 123,
  "old_values": {
    "status": "aberto"
  },
  "new_values": {
    "status": "resolvido"
  },
  "description": "Status do ticket 'Problema no sistema' alterado de 'aberto' para 'resolvido'",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0...",
  "metadata": {
    "old_status": "aberto",
    "new_status": "resolvido"
  },
  "created_at": "2025-11-28T14:20:00.000000Z",
  "updated_at": "2025-11-28T14:20:00.000000Z",
  "user": {
    "id": 5,
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "support"
  }
}
```

---

### 3. Logs de um Usuário Específico

**Rota:** `GET /api/activity-logs/user/{userId}`

**Descrição:** Retorna todos os logs de atividades de um usuário específico.

**Parâmetros de Query (opcionais):**
- `period` - Filtrar por período: `day`, `week`, `month`, `year`, `all`
- `per_page` - Itens por página (padrão: 50, máximo: 100)

**Permissões:**
- ✅ Admin/Support/Assistant: Pode ver logs de qualquer usuário
- ✅ Cliente: Só pode ver seus próprios logs

**Exemplo de Requisição:**
```
GET /api/activity-logs/user/5?period=week&per_page=30
```

**Estrutura da Resposta:**
Mesma estrutura da rota "Listar Todos os Logs", mas filtrada por usuário.

---

### 4. Logs de um Ticket Específico

**Rota:** `GET /api/activity-logs/ticket/{ticketId}`

**Descrição:** Retorna todos os logs relacionados a um ticket específico.

**Parâmetros de Query (opcionais):**
- `per_page` - Itens por página (padrão: 50, máximo: 100)

**Permissões:**
- ✅ Usuário precisa ter acesso ao ticket (mesmas regras de visualização de tickets)

**Exemplo de Requisição:**
```
GET /api/activity-logs/ticket/123?per_page=20
```

**Estrutura da Resposta:**
Mesma estrutura da rota "Listar Todos os Logs", mas filtrada por ticket.

**Uso Recomendado:**
- Mostrar timeline de atividades no detalhe do ticket
- Limitar a 20-30 itens mais recentes para performance

---

### 5. Estatísticas dos Logs

**Rota:** `GET /api/activity-logs/stats`

**Descrição:** Retorna estatísticas agregadas dos logs.

**Parâmetros de Query (opcionais):**
- `period` - Filtrar por período: `day`, `week`, `month`, `year`, `all`

**Permissões:**
- ✅ Admin/Support/Assistant: Vê estatísticas de todos
- ✅ Cliente: Vê apenas suas próprias estatísticas

**Exemplo de Requisição:**
```
GET /api/activity-logs/stats?period=month
```

**Estrutura da Resposta:**
```json
{
  "total": 1250,
  "by_action": {
    "created": 450,
    "updated": 600,
    "deleted": 50,
    "viewed": 100,
    "assigned": 30,
    "status_changed": 20
  },
  "by_model_type": {
    "App\\Models\\Ticket": 800,
    "App\\Models\\TicketMessage": 400,
    "App\\Models\\TicketAttachment": 50
  }
}
```

---

## 📊 Tipos de Ações (Actions)

Os logs registram diferentes tipos de ações:

| Ação | Descrição | Quando é registrado |
|------|-----------|---------------------|
| `created` | Criação | Quando um ticket, mensagem ou anexo é criado |
| `updated` | Atualização | Quando qualquer campo é atualizado |
| `deleted` | Deleção | Quando um item é deletado |
| `viewed` | Visualização | Quando um ticket é visualizado (uma vez por sessão) |
| `assigned` | Atribuição | Quando um ticket é atribuído a um usuário |
| `status_changed` | Mudança de Status | Quando o status de um ticket muda |

---

## 🎨 Como Usar os Dados

### 1. Timeline de Atividades do Ticket

**Onde mostrar:** Na página de detalhes do ticket

**Dados necessários:**
```
GET /api/activity-logs/ticket/{ticketId}?per_page=30
```

**O que mostrar:**
- Lista cronológica de ações (mais recente primeiro)
- Ícone/emoji por tipo de ação
- Nome do usuário que fez a ação
- Descrição da ação
- Data/hora formatada

**Exemplo Visual:**
```
📋 Timeline do Ticket #123

🟢 Criado por João Silva
   há 2 dias às 10:30

✏️ Atualizado por Maria Santos
   há 1 dia às 14:20

🔄 Status alterado: aberto → resolvido
   por Maria Santos há 1 dia às 15:00

💬 Mensagem enviada por João Silva
   há 12 horas às 08:00

📎 Anexo adicionado por Maria Santos
   há 6 horas às 14:00
```

---

### 2. Feed de Atividades Pessoais

**Onde mostrar:** Dashboard pessoal ou página de perfil

**Dados necessários:**
```
GET /api/activity-logs/user/{userId}?period=week&per_page=50
```

**O que mostrar:**
- Últimas atividades do usuário logado
- Agrupadas por dia ou tipo
- Links para os tickets relacionados

**Exemplo Visual:**
```
📊 Suas Atividades Recentes

Hoje
├─ ✅ Criou ticket #456 (há 1 hora)
├─ 💬 Enviou mensagem no ticket #123 (há 2 horas)
└─ 📎 Adicionou anexo no ticket #789 (há 3 horas)

Ontem
├─ ✅ Criou 3 tickets
├─ ✏️ Atualizou 5 tickets
└─ 💬 Enviou 8 mensagens
```

---

### 3. Dashboard de Auditoria (Admin)

**Onde mostrar:** Página dedicada de auditoria (apenas admin)

**Dados necessários:**
```
GET /api/activity-logs/stats?period=day
GET /api/activity-logs?period=day&per_page=100
```

**O que mostrar:**
- Estatísticas gerais (total, por ação, por tipo)
- Lista completa de atividades com filtros
- Gráficos de atividades ao longo do tempo
- Top usuários mais ativos

**Exemplo Visual:**
```
📈 Auditoria do Sistema

Estatísticas Hoje
├─ Total de ações: 245
├─ Por ação:
│  ├─ Criados: 45 tickets
│  ├─ Atualizados: 120 tickets
│  ├─ Mensagens: 80
│  └─ Deletados: 0
└─ Top usuários: João (50), Maria (45), Pedro (30)

Lista de Atividades
[Filtros: Período | Ação | Usuário | Tipo]
[Ordenar por: Data | Usuário | Ação]
```

---

### 4. Histórico de Mudanças

**Onde mostrar:** Modal ou seção expandível no detalhe do ticket

**Dados necessários:**
```
GET /api/activity-logs/ticket/{ticketId}?action=updated&per_page=50
```

**O que mostrar:**
- Mudanças específicas (old_values → new_values)
- Comparação lado a lado
- Destaque para mudanças importantes (status, atribuição)

**Exemplo Visual:**
```
📝 Histórico de Mudanças

Status: aberto → resolvido
   por Maria Santos há 1 dia

Prioridade: média → alta
   por João Silva há 2 dias

Atribuído para: Pedro → Maria
   por Admin há 3 dias
```

---

## 🎯 Casos de Uso Práticos

### Caso 1: Ver quem resolveu um ticket
```
GET /api/activity-logs/ticket/123?action=status_changed
```
Buscar logs onde `action = status_changed` e `new_values.status = resolvido`

### Caso 2: Ver atividades de um usuário hoje
```
GET /api/activity-logs/user/5?period=day
```
OU
```
GET /api/activity-logs?user_id=5&period=day
```
Mostrar todas as ações do usuário no dia atual

### Caso 3: Ver atividades de um usuário específico (escolher de quem ver)
```
GET /api/activity-logs?user_id=5&period=month
```
OU
```
GET /api/activity-logs/user/5?period=month
```
**Ambas as rotas funcionam!** Use a que preferir.

### Caso 4: Ver quantos tickets foram criados hoje
```
GET /api/activity-logs/stats?period=day
```
Ver em `by_action.created` e `by_model_type["App\\Models\\Ticket"]`

### Caso 5: Timeline completa de um ticket
```
GET /api/activity-logs/ticket/123?per_page=100
```
Mostrar todas as ações relacionadas ao ticket em ordem cronológica

### Caso 6: Ver logs de um usuário com múltiplos filtros
```
GET /api/activity-logs?user_id=5&action=created&period=week&model_type=App\Models\Ticket
```
Ver apenas tickets criados por um usuário específico na última semana

---

## 💡 Boas Práticas

### 1. Performance
- ✅ **Sempre paginar**: Não buscar mais de 50-100 itens por vez
- ✅ **Usar períodos**: Filtrar por período padrão (últimos 7-30 dias)
- ✅ **Lazy loading**: Carregar mais itens sob demanda
- ✅ **Cache**: Cachear estatísticas por alguns minutos

### 2. UX
- ✅ **Ícones visuais**: Usar ícones/emojis para cada tipo de ação
- ✅ **Cores**: Verde para criado, vermelho para deletado, azul para atualizado
- ✅ **Datas legíveis**: "há 2 horas" em vez de "2025-11-28T10:30:00"
- ✅ **Tooltips**: Mostrar detalhes completos ao passar o mouse
- ✅ **Filtros**: Permitir filtrar por ação, período, usuário

### 3. Segurança
- ✅ **Respeitar permissões**: Clientes só veem seus próprios logs
- ✅ **Não mostrar dados sensíveis**: IP, user agent apenas para admin
- ✅ **Validar acesso**: Verificar se usuário tem acesso ao ticket antes de mostrar logs

### 4. Informações a Mostrar

**Sempre mostrar:**
- Tipo de ação (com ícone)
- Usuário que fez a ação
- Descrição da ação
- Data/hora formatada

**Mostrar opcionalmente (expandível):**
- Valores antes/depois (old_values/new_values)
- Metadados completos
- IP e user agent (apenas admin)

**Não mostrar:**
- Logs técnicos demais
- Dados sensíveis para clientes
- Logs de visualização em massa (pode ser spam)

---

## 📋 Estrutura de Dados Detalhada

### Campo: `action`
Tipo: `string`
Valores possíveis: `created`, `updated`, `deleted`, `viewed`, `assigned`, `status_changed`

### Campo: `model_type`
Tipo: `string`
Valores comuns:
- `App\Models\Ticket` - Logs de tickets
- `App\Models\TicketMessage` - Logs de mensagens
- `App\Models\TicketAttachment` - Logs de anexos de tickets
- `App\Models\MessageAttachment` - Logs de anexos de mensagens

### Campo: `old_values` e `new_values`
Tipo: `object` ou `null`
Contém os valores antes e depois da mudança (apenas para ações `updated`, `status_changed`)

**Exemplo:**
```json
{
  "old_values": {
    "status": "aberto",
    "priority": "média"
  },
  "new_values": {
    "status": "resolvido",
    "priority": "alta"
  }
}
```

### Campo: `metadata`
Tipo: `object` ou `null`
Contém informações extras específicas da ação

**Exemplos:**
- Para `assigned`: `{"assigned_to_user_id": 5}`
- Para `status_changed`: `{"old_status": "aberto", "new_status": "resolvido"}`
- Para `created`: `{"cliente_id": 10, "assigned_to": 5}`

### Campo: `user`
Tipo: `object`
Informações do usuário que realizou a ação:
```json
{
  "id": 5,
  "name": "João Silva",
  "email": "joao@example.com",
  "role": "support"
}
```

---

## 🔄 Integração com Outras Rotas

### Combinar com Estatísticas
Use a rota de logs junto com `/api/statistics/my-activity`:
- `my-activity`: Dados agregados e timeline simplificada
- `activity-logs`: Dados detalhados com auditoria completa

### Combinar com Tickets
Ao mostrar detalhes de um ticket (`/api/ticket/{id}`), use:
```
GET /api/activity-logs/ticket/{id}?per_page=20
```
Para mostrar timeline de atividades no mesmo componente.

---

## ⚠️ Observações Importantes

1. **Paginação**: Sempre use paginação para evitar sobrecarga
2. **Períodos**: Use períodos razoáveis (não buscar "all" sem necessidade)
3. **Permissões**: Respeite as permissões do usuário logado
4. **Performance**: Cache estatísticas e limite resultados
5. **UX**: Formate datas e mostre informações de forma legível

---

## 📝 Resumo das Rotas

| Rota | Uso Principal | Quando Usar |
|------|---------------|-------------|
| `GET /api/activity-logs` | Lista geral | Dashboard de auditoria, busca geral |
| `GET /api/activity-logs?user_id={id}` | Filtrar por usuário | Escolher de quem ver os logs |
| `GET /api/activity-logs/{id}` | Detalhes | Ver informações completas de um log |
| `GET /api/activity-logs/user/{userId}` | Atividades do usuário | Perfil, dashboard pessoal (alternativa ao user_id) |
| `GET /api/activity-logs/ticket/{ticketId}` | Timeline do ticket | Detalhes do ticket |
| `GET /api/activity-logs/stats` | Estatísticas | Dashboard, gráficos |

---

## 💡 Como Escolher de Quem Ver os Logs

### Opção 1: Usar parâmetro `user_id` na rota principal
```
GET /api/activity-logs?user_id=5&period=month
```
**Vantagem:** Pode combinar com outros filtros facilmente

### Opção 2: Usar rota específica `/user/{userId}`
```
GET /api/activity-logs/user/5?period=month
```
**Vantagem:** URL mais semântica e clara

**Ambas funcionam igual!** Escolha a que preferir.

### Exemplo Prático no Frontend:

**Componente de Seleção de Usuário:**
```javascript
// Usuário seleciona um usuário do dropdown
const selectedUserId = 5;

// Opção 1: Usar parâmetro
const logs = await fetch(`/api/activity-logs?user_id=${selectedUserId}&period=month`);

// Opção 2: Usar rota específica
const logs = await fetch(`/api/activity-logs/user/${selectedUserId}?period=month`);
```

**Com Filtros Múltiplos:**
```javascript
// Ver apenas tickets criados por um usuário específico
const logs = await fetch(
  `/api/activity-logs?user_id=5&action=created&model_type=App\\Models\\Ticket&period=week`
);
```

**Permissões:**
- ✅ **Admin/Support/Assistant:** Podem escolher qualquer usuário
- ❌ **Cliente:** Só pode ver seus próprios logs (sistema força automaticamente)

---

---

## 🎨 Exemplos Práticos de Uso no Frontend

### Exemplo 1: Componente de Seleção de Usuário

**Cenário:** Dashboard onde admin pode escolher ver logs de qualquer usuário

```javascript
// Componente React/Vue/etc
const [selectedUserId, setSelectedUserId] = useState(null);
const [logs, setLogs] = useState([]);
const [loading, setLoading] = useState(false);

// Função para buscar logs do usuário selecionado
const fetchUserLogs = async (userId, period = 'month') => {
  setLoading(true);
  try {
    // Opção 1: Usar parâmetro user_id
    const response = await fetch(
      `/api/activity-logs?user_id=${userId}&period=${period}&per_page=50`,
      {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );
    
    // OU Opção 2: Usar rota específica
    // const response = await fetch(
    //   `/api/activity-logs/user/${userId}?period=${period}&per_page=50`,
    //   {
    //     headers: {
    //       'Authorization': `Bearer ${token}`
    //     }
    //   }
    // );
    
    const data = await response.json();
    setLogs(data.data);
  } catch (error) {
    console.error('Erro ao buscar logs:', error);
  } finally {
    setLoading(false);
  }
};

// Quando usuário selecionar outro usuário
const handleUserChange = (userId) => {
  setSelectedUserId(userId);
  fetchUserLogs(userId);
};

// Render
return (
  <div>
    <select onChange={(e) => handleUserChange(e.target.value)}>
      <option value="">Todos os usuários</option>
      <option value="1">João Silva</option>
      <option value="2">Maria Santos</option>
      <option value="3">Pedro Costa</option>
    </select>
    
    {loading ? (
      <p>Carregando...</p>
    ) : (
      <LogsList logs={logs} />
    )}
  </div>
);
```

---

### Exemplo 2: Filtros Múltiplos

**Cenário:** Ver apenas tickets criados por um usuário específico

```javascript
const fetchFilteredLogs = async (userId, action, period) => {
  const params = new URLSearchParams({
    user_id: userId,
    action: action,
    period: period,
    model_type: 'App\\Models\\Ticket',
    per_page: '50'
  });
  
  const response = await fetch(
    `/api/activity-logs?${params.toString()}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }
  );
  
  return await response.json();
};

// Uso: Ver apenas tickets criados pelo usuário 5 na última semana
const logs = await fetchFilteredLogs(5, 'created', 'week');
```

---

### Exemplo 3: Timeline de Atividades do Ticket

**Cenário:** Mostrar todas as ações de um ticket, incluindo quem fez cada ação

```javascript
const fetchTicketLogs = async (ticketId) => {
  const response = await fetch(
    `/api/activity-logs/ticket/${ticketId}?per_page=30`,
    {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }
  );
  
  const data = await response.json();
  return data.data;
};

// Render timeline
const TicketTimeline = ({ ticketId }) => {
  const [logs, setLogs] = useState([]);
  
  useEffect(() => {
    fetchTicketLogs(ticketId).then(setLogs);
  }, [ticketId]);
  
  return (
    <div className="timeline">
      {logs.map(log => (
        <div key={log.id} className="timeline-item">
          <span className="action-icon">{getActionIcon(log.action)}</span>
          <div>
            <strong>{log.user.name}</strong>
            <p>{log.description}</p>
            <small>{formatDate(log.created_at)}</small>
          </div>
        </div>
      ))}
    </div>
  );
};
```

---

### Exemplo 4: Ver Logs do Usuário Logado

**Cenário:** Mostrar atividades do próprio usuário

```javascript
const fetchMyLogs = async () => {
  const currentUser = getCurrentUser(); // Função que pega usuário logado
  
  // Opção 1: Usar rota específica
  const response = await fetch(
    `/api/activity-logs/user/${currentUser.id}?period=week`,
    {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }
  );
  
  // OU Opção 2: Usar parâmetro
  // const response = await fetch(
  //   `/api/activity-logs?user_id=${currentUser.id}&period=week`,
  //   {
  //     headers: {
  //       'Authorization': `Bearer ${token}`
  //     }
  //   }
  // );
  
  return await response.json();
};
```

---

### Exemplo 5: Dashboard com Filtros Avançados

**Cenário:** Dashboard completo com múltiplos filtros

```javascript
const ActivityLogsDashboard = () => {
  const [filters, setFilters] = useState({
    user_id: null,
    action: null,
    period: 'month',
    model_type: null
  });
  const [logs, setLogs] = useState([]);
  
  const fetchLogs = async () => {
    const params = new URLSearchParams();
    
    // Adicionar apenas filtros que foram preenchidos
    if (filters.user_id) params.append('user_id', filters.user_id);
    if (filters.action) params.append('action', filters.action);
    if (filters.period) params.append('period', filters.period);
    if (filters.model_type) params.append('model_type', filters.model_type);
    
    params.append('per_page', '50');
    
    const response = await fetch(
      `/api/activity-logs?${params.toString()}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );
    
    const data = await response.json();
    setLogs(data.data);
  };
  
  useEffect(() => {
    fetchLogs();
  }, [filters]);
  
  return (
    <div>
      <div className="filters">
        <select 
          value={filters.user_id || ''} 
          onChange={(e) => setFilters({...filters, user_id: e.target.value || null})}
        >
          <option value="">Todos os usuários</option>
          <option value="1">João Silva</option>
          <option value="2">Maria Santos</option>
        </select>
        
        <select 
          value={filters.action || ''} 
          onChange={(e) => setFilters({...filters, action: e.target.value || null})}
        >
          <option value="">Todas as ações</option>
          <option value="created">Criado</option>
          <option value="updated">Atualizado</option>
          <option value="deleted">Deletado</option>
        </select>
        
        <select 
          value={filters.period} 
          onChange={(e) => setFilters({...filters, period: e.target.value})}
        >
          <option value="day">Hoje</option>
          <option value="week">Esta semana</option>
          <option value="month">Este mês</option>
          <option value="year">Este ano</option>
        </select>
      </div>
      
      <LogsList logs={logs} />
    </div>
  );
};
```

---

### Exemplo 6: Verificar Permissões

**Cenário:** Verificar se usuário pode ver logs de outros usuários

```javascript
const canViewOtherUserLogs = (currentUser) => {
  // Admin, Support e Assistant podem ver logs de qualquer usuário
  return ['admin', 'support', 'assistant'].includes(currentUser.role);
};

// No componente
const ActivityLogsView = () => {
  const currentUser = getCurrentUser();
  const canViewOthers = canViewOtherUserLogs(currentUser);
  
  return (
    <div>
      {canViewOthers ? (
        <UserSelector onSelect={handleUserSelect} />
      ) : (
        <p>Você só pode ver seus próprios logs</p>
      )}
      
      <LogsList />
    </div>
  );
};
```

---

## 🔑 Resumo: Como Escolher de Quem Ver os Logs

### Duas Formas (escolha a que preferir):

**1. Parâmetro `user_id`:**
```
GET /api/activity-logs?user_id=5&period=month
```
✅ Melhor para: Filtros múltiplos, URLs dinâmicas

**2. Rota específica:**
```
GET /api/activity-logs/user/5?period=month
```
✅ Melhor para: URLs semânticas, código mais limpo

### Permissões:
- ✅ **Admin/Support/Assistant:** Podem escolher qualquer usuário
- ❌ **Cliente:** Só pode ver seus próprios logs (sistema força automaticamente)

### Dica:
Use a rota que fizer mais sentido no seu contexto. Ambas retornam exatamente os mesmos dados!

---

**Última atualização:** Novembro 2025

