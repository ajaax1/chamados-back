# 📊 Estatísticas e Novos Dados - Guia para Frontend

## 📋 Visão Geral

Foram implementadas novas funcionalidades de estatísticas e métricas no sistema de chamados. Este documento descreve todas as alterações, novos endpoints e campos disponíveis para o frontend.

---

## 🆕 Novos Campos nos Tickets

### Campo `origem`

**Tipo:** `enum` (opcional)  
**Valores possíveis:**
- `formulario_web` - Ticket criado via formulário web
- `email` - Ticket criado via e-mail
- `api` - Ticket criado via API
- `tel_manual` - Ticket criado manualmente/telefone
- `null` - Não especificado

**Exemplo de uso:**
```javascript
// Criar ticket com origem
const createTicket = async (ticketData) => {
  const response = await api.post('/api/tickets', {
    title: "Problema no sistema",
    nome_cliente: "João Silva",
    descricao: "Sistema está lento",
    status: "aberto",
    priority: "alta",
    origem: "formulario_web" // Novo campo
  });
  return response.data;
};

// Atualizar origem do ticket
const updateTicket = async (ticketId, updates) => {
  const response = await api.put(`/api/tickets/${ticketId}`, {
    origem: "email" // Pode ser atualizado
  });
  return response.data;
};
```

---

## 📈 Novos Endpoints de Estatísticas

### 1. Estatísticas de Tickets (`GET /api/admin/statistics/tickets`)

Endpoint completo com todas as estatísticas de tickets.

**Requisição:**
```javascript
GET /api/admin/statistics/tickets?period=month
```

**Parâmetros:**
- `period` (opcional): `day`, `week`, `month` (padrão: `month`)

**Resposta completa:**
```json
{
  "period": "month",
  "start_date": "2025-10-27T00:00:00.000000Z",
  "overview": {
    "total": 141,
    "abertos": 25,
    "pendentes": 15,
    "resolvidos": 85,
    "finalizados": 16
  },
  "by_status": { ... },
  "by_priority": { ... },
  "by_day": [ ... ],
  "by_user": [ ... ],
  "by_cliente": [ ... ],
  "resolution_time": { ... },
  "resolution_time_by_cliente": [ ... ],
  
  // 🆕 NOVAS ESTATÍSTICAS
  
  "response_time": {
    "first_response": {
      "average_minutes": 45.5,
      "average_hours": 0.76,
      "tickets_with_response": 120,
      "total_tickets": 141
    },
    "resolution_time": {
      "average_minutes": 180.5,
      "average_hours": 3.01,
      "resolved_tickets": 100
    },
    "total_open_time": {
      "average_minutes": 240.5,
      "average_hours": 4.01,
      "average_days": 0.17
    }
  },
  
  "agent_productivity": [
    {
      "user_id": 2,
      "user_name": "João Silva",
      "user_email": "joao@example.com",
      "user_role": "support",
      "tickets_assigned": 35,
      "tickets_closed": 30,
      "tickets_not_resolved": 5,
      "resolution_rate": 85.71,
      "average_response_time_minutes": 30.5,
      "average_response_time_hours": 0.51,
      "average_resolution_time_minutes": 120.5,
      "average_resolution_time_hours": 2.01
    }
  ],
  
  "tickets_by_origin": {
    "total": 141,
    "by_origin": {
      "formulario_web": {
        "total": 50,
        "percentage": 35.46
      },
      "email": {
        "total": 40,
        "percentage": 28.37
      },
      "api": {
        "total": 30,
        "percentage": 21.28
      },
      "tel_manual": {
        "total": 15,
        "percentage": 10.64
      },
      "null": {
        "total": 6,
        "percentage": 4.26
      }
    }
  },
  
  "tickets_created_by_period": [
    {
      "period": "2025-11-27",
      "total": 5
    },
    {
      "period": "2025-11-26",
      "total": 8
    }
  ],
  
  "tickets_closed_by_period": [
    {
      "period": "2025-11-27",
      "created": 5,
      "closed": 4,
      "open": 1
    },
    {
      "period": "2025-11-26",
      "created": 8,
      "closed": 6,
      "open": 2
    }
  ],
  
  "tickets_by_agent_detailed": [
    {
      "user_id": 2,
      "user_name": "João Silva",
      "user_email": "joao@example.com",
      "user_role": "support",
      "tickets_received": 35,
      "tickets_responded": 32,
      "tickets_closed": 30,
      "tickets_not_resolved": 5,
      "response_rate": 91.43,
      "resolution_rate": 85.71
    }
  ]
}
```

---

## 🕒 1. Tempo de Resposta (`response_time`)

### Estrutura:
```typescript
interface ResponseTime {
  first_response: {
    average_minutes: number;      // Tempo médio até primeira resposta (minutos)
    average_hours: number;        // Tempo médio até primeira resposta (horas)
    tickets_with_response: number; // Quantos tickets receberam resposta
    total_tickets: number;        // Total de tickets
  };
  resolution_time: {
    average_minutes: number;      // Tempo médio até solução (minutos)
    average_hours: number;        // Tempo médio até solução (horas)
    resolved_tickets: number;     // Quantos tickets foram resolvidos
  };
  total_open_time: {
    average_minutes: number;      // Tempo médio total aberto (minutos)
    average_hours: number;        // Tempo médio total aberto (horas)
    average_days: number;         // Tempo médio total aberto (dias)
  };
}
```

### Exemplo de uso:
```javascript
const getResponseTimeStats = async () => {
  const response = await api.get('/api/admin/statistics/tickets?period=month');
  const { response_time } = response.data;
  
  console.log(`Tempo médio até primeira resposta: ${response_time.first_response.average_hours}h`);
  console.log(`Tempo médio até solução: ${response_time.resolution_time.average_hours}h`);
  console.log(`Tempo médio total aberto: ${response_time.total_open_time.average_days} dias`);
  
  return response_time;
};
```

### Gráfico sugerido:
- **Gráfico de barras** comparando os três tempos
- **Indicador de performance** (verde/amarelo/vermelho) baseado em metas

---

## 👨‍💻 2. Produtividade dos Agentes (`agent_productivity`)

### Estrutura:
```typescript
interface AgentProductivity {
  user_id: number;
  user_name: string;
  user_email: string;
  user_role: string;
  tickets_assigned: number;              // Tickets atribuídos
  tickets_closed: number;               // Tickets fechados
  tickets_not_resolved: number;         // Tickets não resolvidos
  resolution_rate: number;               // Taxa de resolução (%)
  average_response_time_minutes: number; // Tempo médio de resposta (minutos)
  average_response_time_hours: number;   // Tempo médio de resposta (horas)
  average_resolution_time_minutes: number; // Tempo médio de resolução (minutos)
  average_resolution_time_hours: number;   // Tempo médio de resolução (horas)
}
```

### Exemplo de uso:
```javascript
const getAgentProductivity = async () => {
  const response = await api.get('/api/admin/statistics/tickets?period=month');
  const { agent_productivity } = response.data;
  
  // Ordenar por tickets atribuídos
  const sorted = agent_productivity.sort((a, b) => 
    b.tickets_assigned - a.tickets_assigned
  );
  
  // Filtrar apenas suportes
  const supports = agent_productivity.filter(
    agent => agent.user_role === 'support'
  );
  
  return sorted;
};
```

### Gráficos sugeridos:
- **Tabela de produtividade** com todos os dados
- **Gráfico de barras** comparando tickets atribuídos vs fechados
- **Gráfico de pizza** mostrando taxa de resolução
- **Ranking** dos melhores agentes

---

## 📥 3. Origens dos Tickets (`tickets_by_origin`)

### Estrutura:
```typescript
interface TicketsByOrigin {
  total: number;
  by_origin: {
    formulario_web: {
      total: number;
      percentage: number;
    };
    email: {
      total: number;
      percentage: number;
    };
    api: {
      total: number;
      percentage: number;
    };
    tel_manual: {
      total: number;
      percentage: number;
    };
    null: {
      total: number;
      percentage: number;
    };
  };
}
```

### Exemplo de uso:
```javascript
const getTicketsByOrigin = async () => {
  const response = await api.get('/api/admin/statistics/tickets?period=month');
  const { tickets_by_origin } = response.data;
  
  // Preparar dados para gráfico de pizza
  const chartData = Object.entries(tickets_by_origin.by_origin)
    .filter(([key]) => key !== 'null') // Filtrar null se necessário
    .map(([key, value]) => ({
      name: formatOriginName(key),
      value: value.total,
      percentage: value.percentage
    }));
  
  return chartData;
};

const formatOriginName = (origin) => {
  const names = {
    formulario_web: 'Formulário Web',
    email: 'E-mail',
    api: 'API',
    tel_manual: 'Telefone/Manual'
  };
  return names[origin] || origin;
};
```

### Gráficos sugeridos:
- **Gráfico de pizza** mostrando distribuição por origem
- **Gráfico de barras** comparando quantidades
- **Indicadores** com percentuais

---

## ✔️ 4. Tickets Criados por Período (`tickets_created_by_period`)

### Estrutura:
```typescript
interface TicketsCreatedByPeriod {
  period: string;  // Formato depende do período: "2025-11-27" (day), "202550" (week), "2025-11" (month)
  total: number;
}
```

### Exemplo de uso:
```javascript
const getTicketsCreatedByPeriod = async (period = 'day') => {
  const response = await api.get(`/api/admin/statistics/tickets?period=${period}`);
  const { tickets_created_by_period } = response.data;
  
  // Preparar para gráfico de linha
  const chartData = tickets_created_by_period.map(item => ({
    x: formatPeriod(item.period, period),
    y: item.total
  }));
  
  return chartData;
};

const formatPeriod = (period, type) => {
  if (type === 'day') {
    return new Date(period).toLocaleDateString('pt-BR');
  } else if (type === 'week') {
    return `Semana ${period}`;
  } else {
    return period; // "2025-11"
  }
};
```

### Gráficos sugeridos:
- **Gráfico de linha** mostrando tendência ao longo do tempo
- **Gráfico de barras** mostrando picos de atendimento
- **Heatmap** mostrando horários mais movimentados

---

## ✔️ 5. Tickets Fechados por Período (`tickets_closed_by_period`)

### Estrutura:
```typescript
interface TicketsClosedByPeriod {
  period: string;
  created: number;  // Tickets criados no período
  closed: number;   // Tickets fechados no período
  open: number;     // Tickets ainda abertos (created - closed)
}
```

### Exemplo de uso:
```javascript
const getTicketsClosedByPeriod = async (period = 'day') => {
  const response = await api.get(`/api/admin/statistics/tickets?period=${period}`);
  const { tickets_closed_by_period } = response.data;
  
  // Preparar para gráfico comparativo
  const chartData = tickets_closed_by_period.map(item => ({
    period: formatPeriod(item.period, period),
    criados: item.created,
    fechados: item.closed,
    abertos: item.open
  }));
  
  return chartData;
};
```

### Gráficos sugeridos:
- **Gráfico de barras agrupadas** comparando criados vs fechados
- **Gráfico de linha** mostrando tendência de abertos vs fechados
- **Indicador de backlog** (tickets abertos acumulados)

---

## ✔️ 6. Tickets por Agente Detalhado (`tickets_by_agent_detailed`)

### Estrutura:
```typescript
interface TicketsByAgentDetailed {
  user_id: number;
  user_name: string;
  user_email: string;
  user_role: string;
  tickets_received: number;      // Tickets recebidos
  tickets_responded: number;     // Tickets respondidos
  tickets_closed: number;        // Tickets fechados
  tickets_not_resolved: number;  // Tickets não resolvidos
  response_rate: number;          // Taxa de resposta (%)
  resolution_rate: number;        // Taxa de resolução (%)
}
```

### Exemplo de uso:
```javascript
const getTicketsByAgentDetailed = async () => {
  const response = await api.get('/api/admin/statistics/tickets?period=month');
  const { tickets_by_agent_detailed } = response.data;
  
  // Calcular métricas adicionais
  const enhanced = tickets_by_agent_detailed.map(agent => ({
    ...agent,
    pendingTickets: agent.tickets_received - agent.tickets_responded,
    efficiency: (agent.tickets_closed / agent.tickets_received) * 100
  }));
  
  return enhanced;
};
```

### Gráficos sugeridos:
- **Tabela detalhada** com todas as métricas
- **Gráfico de barras** comparando recebidos, respondidos e fechados
- **Gráfico de radar** mostrando múltiplas métricas por agente
- **Cards de resumo** por agente

---

## 📝 Exemplo Completo de Implementação

### React/Next.js com Axios:

```javascript
import axios from 'axios';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Adicionar token de autenticação
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Hook para estatísticas
export const useStatistics = (period = 'month') => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchStatistics = async () => {
      try {
        setLoading(true);
        const response = await api.get(`/admin/statistics/tickets?period=${period}`);
        setData(response.data);
        setError(null);
      } catch (err) {
        setError(err.response?.data?.message || 'Erro ao carregar estatísticas');
      } finally {
        setLoading(false);
      }
    };

    fetchStatistics();
  }, [period]);

  return { data, loading, error };
};

// Componente de exemplo
const StatisticsDashboard = () => {
  const { data, loading, error } = useStatistics('month');

  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!data) return null;

  return (
    <div>
      {/* Tempo de Resposta */}
      <ResponseTimeCard data={data.response_time} />
      
      {/* Produtividade dos Agentes */}
      <AgentProductivityTable data={data.agent_productivity} />
      
      {/* Origens dos Tickets */}
      <TicketsOriginChart data={data.tickets_by_origin} />
      
      {/* Tickets por Período */}
      <TicketsPeriodChart 
        created={data.tickets_created_by_period}
        closed={data.tickets_closed_by_period}
      />
      
      {/* Tickets por Agente */}
      <AgentDetailedTable data={data.tickets_by_agent_detailed} />
    </div>
  );
};
```

---

## 🎨 Sugestões de Visualização

### 1. Dashboard Principal
- **Cards de resumo** com métricas principais
- **Gráficos interativos** com filtros de período
- **Tabelas ordenáveis** para análises detalhadas

### 2. Página de Produtividade
- **Ranking de agentes** por diferentes métricas
- **Comparação entre agentes** (gráficos lado a lado)
- **Filtros** por período, role, etc.

### 3. Página de Origens
- **Gráfico de pizza** interativo
- **Tendência ao longo do tempo** por origem
- **Comparação** entre períodos

### 4. Página de Tempo de Resposta
- **Gráfico de linha** mostrando evolução
- **Metas e SLAs** visualizados
- **Alertas** quando metas não são atingidas

---

## 🔄 Atualização do Campo `origem` nos Tickets

### Ao criar ticket:
```javascript
const createTicket = async (ticketData) => {
  const response = await api.post('/api/tickets', {
    ...ticketData,
    origem: 'formulario_web' // Adicionar origem
  });
  return response.data;
};
```

### Ao atualizar ticket:
```javascript
const updateTicket = async (ticketId, updates) => {
  const response = await api.put(`/api/tickets/${ticketId}`, {
    ...updates,
    origem: 'email' // Pode atualizar origem
  });
  return response.data;
};
```

### Validação no frontend:
```javascript
const validateOrigin = (origin) => {
  const validOrigins = ['formulario_web', 'email', 'api', 'tel_manual'];
  return validOrigins.includes(origin) || origin === null;
};
```

---

## 📊 Formatação de Dados para Gráficos

### Para Chart.js:
```javascript
const prepareChartData = (data) => {
  return {
    labels: data.map(item => item.user_name),
    datasets: [{
      label: 'Tickets Fechados',
      data: data.map(item => item.tickets_closed),
      backgroundColor: 'rgba(54, 162, 235, 0.5)',
    }]
  };
};
```

### Para Recharts:
```javascript
const prepareRechartsData = (data) => {
  return data.map(item => ({
    name: item.user_name,
    tickets: item.tickets_closed,
    taxa: item.resolution_rate
  }));
};
```

---

## ⚠️ Observações Importantes

1. **Autenticação**: Todos os endpoints de estatísticas requerem autenticação de admin
2. **Períodos**: Use `day`, `week` ou `month` no parâmetro `period`
3. **Formato de datas**: As datas retornadas estão no formato ISO 8601
4. **Percentuais**: Todos os percentuais são números (ex: 85.71 = 85.71%)
5. **Tempos**: Todos os tempos estão em minutos e horas (converter conforme necessário)

---

## 📊 Dados Disponíveis para Gráficos

### ✅ Dados Disponíveis no Endpoint `/admin/statistics/tickets`

### 1. 👨‍💻 Desempenho por Agente

**Disponível em:** `agent_productivity` e `tickets_by_agent_detailed`

#### Estrutura dos dados:

```javascript
// agent_productivity
[
  {
    "user_id": 2,
    "user_name": "João Silva",
    "user_email": "joao@example.com",
    "user_role": "support",
    "tickets_assigned": 35,              // ✅ Tickets atribuídos
    "tickets_closed": 30,                // ✅ Tickets fechados
    "tickets_not_resolved": 5,           // ✅ Tickets não resolvidos
    "resolution_rate": 85.71,            // ✅ Taxa de resolução (%)
    "average_response_time_minutes": 30.5,  // ✅ Tempo médio de resposta (minutos)
    "average_response_time_hours": 0.51,    // ✅ Tempo médio de resposta (horas)
    "average_resolution_time_minutes": 120.5, // ✅ Tempo médio de resolução (minutos)
    "average_resolution_time_hours": 2.01     // ✅ Tempo médio de resolução (horas)
  }
]

// tickets_by_agent_detailed
[
  {
    "user_id": 2,
    "user_name": "João Silva",
    "user_email": "joao@example.com",
    "user_role": "support",
    "tickets_received": 35,              // ✅ Tickets recebidos
    "tickets_responded": 32,             // ✅ Tickets respondidos
    "tickets_closed": 30,                // ✅ Tickets fechados
    "tickets_not_resolved": 5,           // ✅ Tickets não resolvidos
    "response_rate": 91.43,              // ✅ Taxa de resposta (%)
    "resolution_rate": 85.71             // ✅ Taxa de resolução (%)
  }
]
```

#### ✅ Dados disponíveis para gráficos:

- ✅ **Tickets atendidos por agente** → `tickets_assigned` ou `tickets_received`
- ✅ **Tempo médio de resposta por agente** → `average_response_time_hours` ou `average_response_time_minutes`
- ⚠️ **SLA violado por agente** → Não disponível diretamente (precisa calcular comparando com meta)
- ✅ **Taxa de resolução por agente** → `resolution_rate`

#### Exemplo de uso:

```javascript
import { getTicketsStats } from '@/services/statistics'

const data = await getTicketsStats('month')

// Gráfico de barras: Tickets atendidos por agente
const chartData = data.agent_productivity.map(agent => ({
  name: agent.user_name,
  tickets: agent.tickets_assigned
}))

// Gráfico de barras: Tempo médio de resposta
const responseTimeData = data.agent_productivity.map(agent => ({
  name: agent.user_name,
  hours: agent.average_response_time_hours
}))

// Gráfico de barras: Taxa de resolução
const resolutionRateData = data.agent_productivity.map(agent => ({
  name: agent.user_name,
  rate: agent.resolution_rate
}))

// Calcular SLA violado (exemplo: meta de 2 horas)
const slaViolatedData = data.agent_productivity.map(agent => {
  const metaHoras = 2; // Meta de SLA
  return {
    name: agent.user_name,
    violouSLA: agent.average_response_time_hours > metaHoras,
    tempoMedio: agent.average_response_time_hours,
    meta: metaHoras
  }
})
```

---

### 2. 🎯 Prioridade dos Tickets

**Disponível em:** `by_priority`

#### Estrutura dos dados:

```javascript
{
  "by_priority": {
    "baixa": {
      "total": 50,
      "percentage": 35.46
    },
    "média": {
      "total": 40,
      "percentage": 28.37
    },
    "alta": {
      "total": 30,
      "percentage": 21.28
    }
    // Nota: "critica" pode não estar disponível se não houver tickets com essa prioridade
  }
}
```

#### ✅ Dados disponíveis para gráficos:

- ✅ **Baixa** → `by_priority.baixa.total` e `by_priority.baixa.percentage`
- ✅ **Média** → `by_priority.média.total` e `by_priority.média.percentage`
- ✅ **Alta** → `by_priority.alta.total` e `by_priority.alta.percentage`
- ⚠️ **Crítica** → Não disponível (sistema atual só tem baixa, média, alta)

#### Exemplo de uso:

```javascript
import { getTicketsStats } from '@/services/statistics'

const data = await getTicketsStats('month')

// Gráfico de pizza: Prioridade dos Tickets
const priorityData = Object.entries(data.by_priority || {}).map(([key, value]) => ({
  name: key === 'baixa' ? 'Baixa' : key === 'média' ? 'Média' : 'Alta',
  value: value.total,
  percentage: value.percentage
}))

// Gráfico de barras: Prioridade dos Tickets
const priorityBarData = [
  { name: 'Baixa', total: data.by_priority.baixa?.total || 0 },
  { name: 'Média', total: data.by_priority.média?.total || 0 },
  { name: 'Alta', total: data.by_priority.alta?.total || 0 }
]

// Gráfico de barras com percentuais
const priorityBarWithPercentage = Object.entries(data.by_priority || {}).map(([key, value]) => ({
  name: key === 'baixa' ? 'Baixa' : key === 'média' ? 'Média' : 'Alta',
  total: value.total,
  percentage: value.percentage
}))
```

---

### 3. 🏢 Tickets por Departamento

**Status:** ❌ **NÃO DISPONÍVEL**

O endpoint atual **não retorna** dados de departamentos. O sistema não possui campo de departamento nos tickets.

#### Opções para implementar:

1. **Adicionar campo `departamento` no backend** (tabela `tickets`)
2. **Usar campo `user.role` como proxy** (mas não é ideal, pois role não é departamento)
3. **Criar relacionamento** entre tickets e departamentos

#### Dados alternativos disponíveis:

- ✅ **Tickets por usuário** → `by_user` (mas não é departamento)
- ✅ **Tickets por cliente** → `by_cliente` (mas não é departamento)

#### Exemplo usando role como proxy (não recomendado):

```javascript
// ⚠️ ATENÇÃO: Isso não é ideal, pois role não é departamento
const data = await getTicketsStats('month')

// Agrupar por role (não é departamento real)
const byRole = data.agent_productivity.reduce((acc, agent) => {
  const role = agent.user_role;
  if (!acc[role]) {
    acc[role] = { total: 0, agents: [] };
  }
  acc[role].total += agent.tickets_assigned;
  acc[role].agents.push(agent.user_name);
  return acc;
}, {})

const roleData = Object.entries(byRole).map(([role, data]) => ({
  name: role === 'admin' ? 'Administração' : 
        role === 'support' ? 'Suporte' : 
        role === 'assistant' ? 'Assistência' : role,
  total: data.total
}))
```

---

### 4. 📥 Origens dos Tickets

**Disponível em:** `tickets_by_origin`

#### Estrutura dos dados:

```javascript
{
  "tickets_by_origin": {
    "total": 141,
    "by_origin": {
      "formulario_web": {
        "total": 50,
        "percentage": 35.46
      },
      "email": {
        "total": 40,
        "percentage": 28.37
      },
      "api": {
        "total": 30,
        "percentage": 21.28
      },
      "tel_manual": {
        "total": 15,
        "percentage": 10.64
      },
      "null": {
        "total": 6,
        "percentage": 4.26
      }
    }
  }
}
```

#### Exemplo de uso:

```javascript
const data = await getTicketsStats('month')

// Gráfico de pizza: Origens dos Tickets
const originData = Object.entries(data.tickets_by_origin.by_origin)
  .filter(([key]) => key !== 'null') // Filtrar null se necessário
  .map(([key, value]) => ({
    name: formatOriginName(key),
    value: value.total,
    percentage: value.percentage
  }))

const formatOriginName = (origin) => {
  const names = {
    formulario_web: 'Formulário Web',
    email: 'E-mail',
    api: 'API',
    tel_manual: 'Telefone/Manual'
  };
  return names[origin] || origin;
}
```

---

### 5. ⏱️ Tempo de Resposta

**Disponível em:** `response_time`

#### Estrutura dos dados:

```javascript
{
  "response_time": {
    "first_response": {
      "average_minutes": 45.5,
      "average_hours": 0.76,
      "tickets_with_response": 120,
      "total_tickets": 141
    },
    "resolution_time": {
      "average_minutes": 180.5,
      "average_hours": 3.01,
      "resolved_tickets": 100
    },
    "total_open_time": {
      "average_minutes": 240.5,
      "average_hours": 4.01,
      "average_days": 0.17
    }
  }
}
```

#### Exemplo de uso:

```javascript
const data = await getTicketsStats('month')

// Gráfico de barras: Comparação de tempos
const timeComparisonData = [
  {
    name: 'Primeira Resposta',
    hours: data.response_time.first_response.average_hours
  },
  {
    name: 'Resolução',
    hours: data.response_time.resolution_time.average_hours
  },
  {
    name: 'Tempo Total Aberto',
    hours: data.response_time.total_open_time.average_hours
  }
]

// Indicador de SLA (exemplo: meta de 2 horas para primeira resposta)
const slaFirstResponse = {
  meta: 2, // horas
  atual: data.response_time.first_response.average_hours,
  status: data.response_time.first_response.average_hours <= 2 ? 'ok' : 'violado'
}
```

---

## 📋 Resumo de Dados Disponíveis

| Gráfico | Dados Disponíveis | Status |
|---------|------------------|--------|
| **Tickets atendidos por agente** | ✅ `agent_productivity.tickets_assigned` | ✅ Disponível |
| **Tempo médio de resposta por agente** | ✅ `agent_productivity.average_response_time_hours` | ✅ Disponível |
| **SLA violado por agente** | ⚠️ Precisa calcular comparando com meta | ⚠️ Parcial |
| **Taxa de resolução por agente** | ✅ `agent_productivity.resolution_rate` | ✅ Disponível |
| **Tickets respondidos por agente** | ✅ `tickets_by_agent_detailed.tickets_responded` | ✅ Disponível |
| **Tickets não resolvidos por agente** | ✅ `agent_productivity.tickets_not_resolved` | ✅ Disponível |
| **Prioridade dos Tickets (Baixa/Média/Alta)** | ✅ `by_priority` | ✅ Disponível |
| **Prioridade Crítica** | ❌ Não existe no sistema | ❌ Não disponível |
| **Tickets por Departamento** | ❌ Campo não existe | ❌ Não disponível |
| **Origens dos Tickets** | ✅ `tickets_by_origin` | ✅ Disponível |
| **Tempo de primeira resposta** | ✅ `response_time.first_response` | ✅ Disponível |
| **Tempo de resolução** | ✅ `response_time.resolution_time` | ✅ Disponível |
| **Tickets criados por período** | ✅ `tickets_created_by_period` | ✅ Disponível |
| **Tickets fechados por período** | ✅ `tickets_closed_by_period` | ✅ Disponível |

---

## 🚀 Exemplo Completo de Implementação

```javascript
import { getTicketsStats } from '@/services/statistics'

// Buscar dados
const stats = await getTicketsStats('month')

// 1. Desempenho por Agente - Tickets Atendidos
const agentTicketsData = stats.agent_productivity.map(agent => ({
  name: agent.user_name,
  tickets: agent.tickets_assigned
}))

// 2. Desempenho por Agente - Tempo de Resposta
const agentResponseTimeData = stats.agent_productivity.map(agent => ({
  name: agent.user_name,
  hours: agent.average_response_time_hours
}))

// 3. Desempenho por Agente - Taxa de Resolução
const agentResolutionRateData = stats.agent_productivity.map(agent => ({
  name: agent.user_name,
  rate: agent.resolution_rate
}))

// 4. Prioridade dos Tickets
const priorityData = Object.entries(stats.by_priority || {}).map(([key, value]) => ({
  name: key === 'baixa' ? 'Baixa' : key === 'média' ? 'Média' : 'Alta',
  total: value.total,
  percentage: value.percentage
}))

// 5. Origens dos Tickets
const originData = Object.entries(stats.tickets_by_origin.by_origin)
  .filter(([key]) => key !== 'null')
  .map(([key, value]) => ({
    name: formatOriginName(key),
    total: value.total,
    percentage: value.percentage
  }))

// 6. Tempo de Resposta Geral
const responseTimeData = {
  primeiraResposta: stats.response_time.first_response.average_hours,
  resolucao: stats.response_time.resolution_time.average_hours,
  tempoTotal: stats.response_time.total_open_time.average_hours
}
```

---

## 📝 Notas Importantes

1. **SLA Violado**: Para calcular, você precisa:
   - Definir uma meta de tempo de resposta (ex: 2 horas)
   - Comparar `average_response_time_hours` com a meta
   - Contar quantos agentes violaram o SLA

2. **Prioridade Crítica**: O sistema atual só tem 3 níveis (baixa, média, alta). Se precisar de "crítica", será necessário adicionar no backend.

3. **Departamentos**: Não existe no sistema atual. Seria necessário adicionar esse campo no backend.

4. **Formato de Prioridade**: O campo `by_priority` usa `"média"` (com acento), não `"media"`. Verifique isso ao acessar os dados.

---

## 🚀 Próximos Passos

1. Implementar componentes de visualização
2. Adicionar filtros interativos
3. Criar exportação de relatórios (PDF/Excel)
4. Implementar notificações quando metas não são atingidas
5. Adicionar comparação entre períodos

---

## 📞 Suporte

Para dúvidas ou problemas, consulte a documentação da API ou entre em contato com a equipe de desenvolvimento.

