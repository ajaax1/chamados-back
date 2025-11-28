# 📊 Guia de Uso das Estatísticas - Frontend

Este guia explica como usar todas as rotas de estatísticas disponíveis no sistema.

---

## 🔐 Autenticação

Todas as rotas requerem autenticação via **Bearer Token** (Sanctum). Adicione o token no header:

```javascript
headers: {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
}
```

---

## 📍 Rotas Disponíveis

### 1. **Estatísticas Pessoais** (Qualquer usuário autenticado)
- `GET /api/statistics/my-stats`

### 2. **Estatísticas Administrativas** (Apenas Admin)
- `GET /api/admin/statistics/dashboard`
- `GET /api/admin/statistics/tickets`
- `GET /api/admin/statistics/users`
- `GET /api/admin/statistics/messages`
- `GET /api/admin/statistics/attachments`

---

## 📅 Parâmetros de Período

Todas as rotas suportam o parâmetro `period` via query string:

- `day` - Hoje
- `week` - Esta semana
- `month` - Este mês (padrão)
- `year` - Este ano
- `all` - Todos os dados

**Exemplo:**
```
GET /api/statistics/my-stats?period=week
```

---

## 1️⃣ Estatísticas Pessoais

### Endpoint
```
GET /api/statistics/my-stats?period=month
```

### Quem pode usar
✅ **Qualquer usuário autenticado** (admin, support, assistant, cliente)

### O que retorna
Estatísticas dos tickets atribuídos ao usuário logado.

### Exemplo de Requisição

```javascript
// Axios
const getMyStats = async (period = 'month') => {
  const token = localStorage.getItem('token');
  const response = await axios.get(`/api/statistics/my-stats?period=${period}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
};

// Fetch
const getMyStats = async (period = 'month') => {
  const token = localStorage.getItem('token');
  const response = await fetch(`/api/statistics/my-stats?period=${period}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return await response.json();
};
```

### Estrutura da Resposta

```json
{
  "period": "month",
  "start_date": "2025-11-01 00:00:00",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "role": "support"
  },
  "overview": {
    "total": 25,
    "abertos": 5,
    "pendentes": 3,
    "resolvidos": 15,
    "finalizados": 2,
    "alta_prioridade": 8,
    "media_prioridade": 12,
    "baixa_prioridade": 5
  },
  "by_status": {
    "aberto": 5,
    "pendente": 3,
    "resolvido": 15,
    "finalizado": 2
  },
  "by_priority": {
    "alta": 8,
    "média": 12,
    "baixa": 5
  },
  "by_day": [
    {
      "date": "2025-11-01",
      "total": 3
    },
    {
      "date": "2025-11-02",
      "total": 5
    }
  ],
  "response_time": {
    "first_response": {
      "average_minutes": 45.5,
      "average_hours": 0.76,
      "tickets_with_response": 20,
      "total_tickets": 25
    },
    "resolution_time": {
      "average_minutes": 180.5,
      "average_hours": 3.01,
      "resolved_tickets": 17
    },
    "total_open_time": {
      "average_minutes": 240.5,
      "average_hours": 4.01,
      "average_days": 0.17
    }
  },
  "productivity": {
    "tickets_assigned": 25,
    "tickets_closed": 17,
    "tickets_not_resolved": 8,
    "tickets_responded": 22,
    "resolution_rate": 68.0,
    "response_rate": 88.0,
    "average_response_time_minutes": 30.5,
    "average_response_time_hours": 0.51,
    "average_resolution_time_minutes": 120.5,
    "average_resolution_time_hours": 2.01
  },
  "tickets_by_origin": {
    "total": 25,
    "by_origin": {
      "formulario_web": {
        "total": 10,
        "percentage": 40.0
      },
      "email": {
        "total": 8,
        "percentage": 32.0
      },
      "api": {
        "total": 5,
        "percentage": 20.0
      },
      "tel_manual": {
        "total": 2,
        "percentage": 8.0
      },
      "null": {
        "total": 0,
        "percentage": 0.0
      }
    }
  },
  "tickets_created_by_period": [
    {
      "period": "2025-11-01",
      "total": 3
    }
  ],
  "tickets_closed_by_period": [
    {
      "period": "2025-11-01",
      "created": 3,
      "closed": 2,
      "open": 1
    }
  ]
}
```

### Exemplo de Uso no React

```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

const MyStatistics = () => {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [period, setPeriod] = useState('month');

  useEffect(() => {
    const fetchStats = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('token');
        const response = await axios.get(`/api/statistics/my-stats?period=${period}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        setStats(response.data);
      } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [period]);

  if (loading) return <div>Carregando...</div>;
  if (!stats) return null;

  return (
    <div>
      <h1>Minhas Estatísticas</h1>
      
      {/* Seletor de período */}
      <select value={period} onChange={(e) => setPeriod(e.target.value)}>
        <option value="day">Hoje</option>
        <option value="week">Esta Semana</option>
        <option value="month">Este Mês</option>
        <option value="year">Este Ano</option>
      </select>

      {/* Cards de resumo */}
      <div className="stats-grid">
        <div className="stat-card">
          <h3>Total de Tickets</h3>
          <p>{stats.overview.total}</p>
        </div>
        <div className="stat-card">
          <h3>Resolvidos</h3>
          <p>{stats.overview.resolvidos}</p>
        </div>
        <div className="stat-card">
          <h3>Taxa de Resolução</h3>
          <p>{stats.productivity.resolution_rate}%</p>
        </div>
        <div className="stat-card">
          <h3>Tempo Médio de Resposta</h3>
          <p>{stats.response_time.first_response.average_hours}h</p>
        </div>
      </div>

      {/* Gráfico de produtividade */}
      <div>
        <h2>Produtividade</h2>
        <p>Tickets Atribuídos: {stats.productivity.tickets_assigned}</p>
        <p>Tickets Fechados: {stats.productivity.tickets_closed}</p>
        <p>Tickets Respondidos: {stats.productivity.tickets_responded}</p>
        <p>Taxa de Resposta: {stats.productivity.response_rate}%</p>
      </div>
    </div>
  );
};

export default MyStatistics;
```

---

## 2️⃣ Dashboard Geral (Admin)

### Endpoint
```
GET /api/admin/statistics/dashboard?period=month
```

### Quem pode usar
🔒 **Apenas Admin**

### O que retorna
Visão geral completa do sistema com todas as métricas principais.

### Exemplo de Requisição

```javascript
const getDashboard = async (period = 'month') => {
  const token = localStorage.getItem('token');
  const response = await axios.get(`/api/admin/statistics/dashboard?period=${period}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
};
```

### Estrutura da Resposta

```json
{
  "period": "month",
  "start_date": "2025-11-01 00:00:00",
  "tickets": {
    "total": 141,
    "abertos": 25,
    "pendentes": 15,
    "resolvidos": 85,
    "finalizados": 16
  },
  "users": {
    "total": 50,
    "admins": 2,
    "support": 10,
    "assistant": 8,
    "cliente": 30
  },
  "messages": {
    "total": 500,
    "internal": 100,
    "external": 400
  },
  "performance": {
    "total_tickets": 141,
    "resolved_tickets": 101,
    "resolution_rate": 71.63,
    "pending_tickets": 15
  },
  "recent_activity": {
    "recent_tickets": [...],
    "recent_messages": [...]
  }
}
```

---

## 3️⃣ Estatísticas de Tickets (Admin)

### Endpoint
```
GET /api/admin/statistics/tickets?period=month
```

### Quem pode usar
🔒 **Apenas Admin**

### O que retorna
Estatísticas detalhadas de todos os tickets do sistema.

### Principais Dados Retornados

- `overview` - Visão geral (total, abertos, pendentes, etc.)
- `by_status` - Agrupado por status
- `by_priority` - Agrupado por prioridade
- `by_day` - Tickets por dia
- `by_user` - Top 10 usuários por tickets
- `by_cliente` - Top 10 clientes por tickets
- `resolution_time` - Tempos de resolução
- `response_time` - Tempos de resposta
- `agent_productivity` - Produtividade dos agentes
- `tickets_by_origin` - Tickets por origem
- `tickets_created_by_period` - Criados por período
- `tickets_closed_by_period` - Fechados por período
- `tickets_by_agent_detailed` - Detalhes por agente

### Exemplo de Uso

```javascript
const getTicketsStats = async (period = 'month') => {
  const token = localStorage.getItem('token');
  const response = await axios.get(`/api/admin/statistics/tickets?period=${period}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
};

// Usar os dados
const stats = await getTicketsStats('month');

// Produtividade dos agentes
const agentProductivity = stats.agent_productivity;
// [
//   {
//     "user_id": 2,
//     "user_name": "João Silva",
//     "tickets_assigned": 35,
//     "tickets_closed": 30,
//     "resolution_rate": 85.71,
//     "average_response_time_hours": 0.51
//   }
// ]

// Gráfico de barras: Tickets por agente
const chartData = agentProductivity.map(agent => ({
  name: agent.user_name,
  tickets: agent.tickets_assigned
}));
```

---

## 4️⃣ Estatísticas de Usuários (Admin)

### Endpoint
```
GET /api/admin/statistics/users?period=month
```

### Quem pode usar
🔒 **Apenas Admin**

### O que retorna
Estatísticas de usuários e performance.

### Principais Dados Retornados

- `overview` - Visão geral de usuários
- `by_role` - Distribuição por role
- `top_performers` - Top 10 por tickets resolvidos
- `user_activity` - Atividade dos usuários
- `resolution_stats_by_user` - Estatísticas de resolução por usuário

---

## 5️⃣ Estatísticas de Mensagens (Admin)

### Endpoint
```
GET /api/admin/statistics/messages?period=month
```

### Quem pode usar
🔒 **Apenas Admin**

### O que retorna
Estatísticas de mensagens do sistema.

### Principais Dados Retornados

- `overview` - Total, internas, externas
- `by_day` - Mensagens por dia
- `by_user` - Top 10 usuários por mensagens
- `internal_vs_external` - Comparação interno vs externo

---

## 6️⃣ Estatísticas de Anexos (Admin)

### Endpoint
```
GET /api/admin/statistics/attachments?period=month
```

### Quem pode usar
🔒 **Apenas Admin**

### O que retorna
Estatísticas de anexos do sistema.

### Principais Dados Retornados

- `overview` - Total, anexos de tickets, anexos de mensagens
- `by_type` - Agrupado por tipo MIME
- `total_size` - Tamanho total (bytes, kb, mb, gb)

---

## 🎨 Exemplos de Visualização

### Hook Personalizado para Estatísticas

```javascript
// hooks/useStatistics.js
import { useState, useEffect } from 'react';
import axios from 'axios';

export const useStatistics = (endpoint, period = 'month', requiresAdmin = false) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        setLoading(true);
        setError(null);
        const token = localStorage.getItem('token');
        const response = await axios.get(`${endpoint}?period=${period}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        setData(response.data);
      } catch (err) {
        setError(err.response?.data?.message || 'Erro ao carregar estatísticas');
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [endpoint, period]);

  return { data, loading, error };
};

// Uso
const MyStatsComponent = () => {
  const { data, loading, error } = useStatistics('/api/statistics/my-stats', 'month');
  
  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!data) return null;

  return (
    <div>
      <h1>Total: {data.overview.total}</h1>
    </div>
  );
};
```

### Componente de Gráfico de Produtividade

```javascript
// components/ProductivityChart.jsx
import { Bar } from 'react-chartjs-2';

const ProductivityChart = ({ agentProductivity }) => {
  const chartData = {
    labels: agentProductivity.map(agent => agent.user_name),
    datasets: [
      {
        label: 'Tickets Atribuídos',
        data: agentProductivity.map(agent => agent.tickets_assigned),
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
      },
      {
        label: 'Tickets Fechados',
        data: agentProductivity.map(agent => agent.tickets_closed),
        backgroundColor: 'rgba(75, 192, 192, 0.5)',
      }
    ]
  };

  return <Bar data={chartData} />;
};
```

---

## ⚠️ Tratamento de Erros

```javascript
const getStats = async () => {
  try {
    const response = await axios.get('/api/statistics/my-stats', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    if (error.response?.status === 401) {
      // Token inválido ou expirado
      localStorage.removeItem('token');
      window.location.href = '/login';
    } else if (error.response?.status === 403) {
      // Sem permissão (não é admin)
      console.error('Acesso negado. Apenas administradores podem acessar.');
    } else {
      console.error('Erro ao carregar estatísticas:', error);
    }
    throw error;
  }
};
```

---

## 📝 Notas Importantes

1. **Autenticação**: Sempre inclua o token no header `Authorization`
2. **Períodos**: Use `day`, `week`, `month`, `year` ou `all`
3. **Permissões**: Rotas `/admin/statistics/*` requerem role `admin`
4. **Formato de Datas**: As datas retornadas estão no formato ISO 8601
5. **Percentuais**: Todos os percentuais são números (ex: 85.71 = 85.71%)
6. **Tempos**: Todos os tempos estão em minutos e horas

---

## 🚀 Próximos Passos

1. Implementar componentes de visualização
2. Adicionar filtros interativos
3. Criar exportação de relatórios (PDF/Excel)
4. Implementar cache para melhor performance
5. Adicionar comparação entre períodos

