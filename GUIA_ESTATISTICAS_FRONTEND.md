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
- `GET /api/statistics/my-stats` - **Suas próprias estatísticas**

### 2. **Estatísticas Administrativas** (Apenas Admin)
- `GET /api/admin/statistics/my-stats` - **Dados pessoais do admin**
- `GET /api/admin/statistics/compare-performance` - **🆕 Comparar sua performance com média dos outros**
- `GET /api/admin/statistics/dashboard` - Dashboard geral do sistema
- `GET /api/admin/statistics/tickets` - Estatísticas detalhadas de tickets
- `GET /api/admin/statistics/users` - Estatísticas de usuários
- `GET /api/admin/statistics/messages` - Estatísticas de mensagens
- `GET /api/admin/statistics/attachments` - Estatísticas de anexos

---

## 🆕 Novas Rotas Implementadas

### ✨ Rota de Comparação de Performance
**`GET /api/admin/statistics/compare-performance`**

Compare sua performance como administrador com a média de todos os outros usuários do sistema.

**Métricas comparadas:**
- ✅ Tickets atribuídos
- ✅ Tickets fechados
- ✅ Taxa de resolução
- ✅ Taxa de resposta
- ✅ Tempo médio de resposta
- ✅ Tempo médio de resolução
- ✅ Tempo de primeira resposta

**Status de comparação:**
- 🟢 `"better"` - Você está significativamente melhor (>10%)
- 🔴 `"worse"` - Você está significativamente pior (>10%)
- 🟡 `"similar"` - Você está similar à média (±10%)

---

## 🚀 Quick Start - Exemplos Rápidos

### 1. Buscar Minhas Estatísticas (Qualquer Usuário)

```javascript
// Hook React
import { useState, useEffect } from 'react';
import axios from 'axios';

const useMyStats = (period = 'month') => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('token');
        const response = await axios.get(`/api/statistics/my-stats?period=${period}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        setData(response.data);
      } catch (err) {
        setError(err.response?.data?.message || 'Erro ao carregar estatísticas');
      } finally {
        setLoading(false);
      }
    };
    fetchStats();
  }, [period]);

  return { data, loading, error };
};

// Uso no componente
const MyStatsComponent = () => {
  const { data, loading, error } = useMyStats('month');
  
  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;
  
  return (
    <div>
      <h2>Minhas Estatísticas</h2>
      <p>Total de Tickets: {data.overview.total}</p>
      <p>Taxa de Resolução: {data.productivity.resolution_rate}%</p>
      <p>Tempo Médio de Resposta: {data.response_time.first_response.average_hours}h</p>
    </div>
  );
};
```

### 2. Comparar Performance (Admin)

```javascript
// Hook React para comparação
const usePerformanceComparison = (period = 'month') => {
  const [comparison, setComparison] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchComparison = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('token');
        const response = await axios.get(`/api/admin/statistics/compare-performance?period=${period}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        setComparison(response.data);
      } catch (err) {
        setError(err.response?.data?.message || 'Erro ao carregar comparação');
      } finally {
        setLoading(false);
      }
    };
    fetchComparison();
  }, [period]);

  return { comparison, loading, error };
};

// Componente de comparação
const PerformanceComparison = () => {
  const { comparison, loading, error } = usePerformanceComparison('month');
  
  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!comparison) return null;

  return (
    <div>
      <h2>Comparação de Performance</h2>
      <div className="comparison-grid">
        {Object.entries(comparison.comparison).map(([key, metric]) => (
          <div key={key} className="metric-card">
            <h3>{key.replace(/_/g, ' ')}</h3>
            <div className="values">
              <div>Meu valor: <strong>{metric.my_value}</strong></div>
              <div>Média: <strong>{metric.average_value}</strong></div>
              <div className={`status ${metric.status}`}>
                {metric.status === 'better' && '🟢 Melhor'}
                {metric.status === 'worse' && '🔴 Pior'}
                {metric.status === 'similar' && '🟡 Similar'}
                {' '}({metric.difference_percent > 0 ? '+' : ''}{metric.difference_percent}%)
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
```

### 3. Serviço Centralizado (Service Pattern)

```javascript
// services/statisticsService.js
import axios from 'axios';

const API_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  return {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  };
};

export const statisticsService = {
  // Estatísticas pessoais (qualquer usuário)
  getMyStats: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/statistics/my-stats?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },

  // Estatísticas pessoais do admin
  getAdminMyStats: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/admin/statistics/my-stats?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },

  // Comparar performance (admin)
  comparePerformance: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/admin/statistics/compare-performance?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },

  // Dashboard geral (admin)
  getDashboard: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/admin/statistics/dashboard?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },

  // Estatísticas de tickets (admin)
  getTicketsStats: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/admin/statistics/tickets?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },

  // Estatísticas de usuários (admin)
  getUsersStats: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/admin/statistics/users?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },

  // Estatísticas de mensagens (admin)
  getMessagesStats: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/admin/statistics/messages?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },

  // Estatísticas de anexos (admin)
  getAttachmentsStats: async (period = 'month') => {
    const response = await axios.get(`${API_URL}/admin/statistics/attachments?period=${period}`, {
      headers: getAuthHeaders()
    });
    return response.data;
  },
};
```

### 4. Uso do Serviço

```javascript
import { statisticsService } from './services/statisticsService';

// Em um componente
const MyComponent = () => {
  const [myStats, setMyStats] = useState(null);
  const [comparison, setComparison] = useState(null);

  useEffect(() => {
    const loadData = async () => {
      try {
        // Carregar minhas estatísticas
        const stats = await statisticsService.getMyStats('month');
        setMyStats(stats);

        // Se for admin, carregar comparação
        if (user.role === 'admin') {
          const comp = await statisticsService.comparePerformance('month');
          setComparison(comp);
        }
      } catch (error) {
        console.error('Erro ao carregar dados:', error);
      }
    };
    loadData();
  }, []);

  return (
    <div>
      {myStats && (
        <div>
          <h3>Meus Tickets: {myStats.overview.total}</h3>
          <p>Taxa de Resolução: {myStats.productivity.resolution_rate}%</p>
        </div>
      )}
      
      {comparison && (
        <div>
          <h3>Comparação</h3>
          <p>Minha taxa: {comparison.comparison.resolution_rate.my_value}%</p>
          <p>Média: {comparison.comparison.resolution_rate.average_value}%</p>
          <p>Status: {comparison.comparison.resolution_rate.status}</p>
        </div>
      )}
    </div>
  );
};
```

---

## 🆕 Novas Rotas Implementadas

### ✨ Rota de Comparação de Performance
**`GET /api/admin/statistics/compare-performance`**

Compare sua performance como administrador com a média de todos os outros usuários do sistema.

**Métricas comparadas:**
- ✅ Tickets atribuídos
- ✅ Tickets fechados
- ✅ Taxa de resolução
- ✅ Taxa de resposta
- ✅ Tempo médio de resposta
- ✅ Tempo médio de resolução
- ✅ Tempo de primeira resposta

**Status de comparação:**
- 🟢 `"better"` - Você está significativamente melhor (>10%)
- 🔴 `"worse"` - Você está significativamente pior (>10%)
- 🟡 `"similar"` - Você está similar à média (±10%)

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

## 2️⃣ Estatísticas Pessoais do Admin

### Endpoint
```
GET /api/admin/statistics/my-stats?period=month
```

### Quem pode usar
🔒 **Apenas Admin**

### O que retorna
Estatísticas pessoais dos tickets atribuídos ao administrador logado. Retorna os mesmos dados da rota `/api/statistics/my-stats`, mas dentro do grupo de rotas administrativas.

### Exemplo de Requisição

```javascript
const getAdminMyStats = async (period = 'month') => {
  const token = localStorage.getItem('token');
  const response = await axios.get(`/api/admin/statistics/my-stats?period=${period}`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
};
```

### Estrutura da Resposta

A estrutura é idêntica à rota `/api/statistics/my-stats`:

```json
{
  "period": "month",
  "start_date": "2025-11-01 00:00:00",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin"
  },
  "overview": { ... },
  "by_status": { ... },
  "by_priority": { ... },
  "by_day": [ ... ],
  "response_time": { ... },
  "productivity": { ... },
  "tickets_by_origin": { ... },
  "tickets_created_by_period": [ ... ],
  "tickets_closed_by_period": [ ... ]
}
```

### Diferença entre as rotas

- `/api/statistics/my-stats` - Qualquer usuário autenticado pode usar
- `/api/admin/statistics/my-stats` - Apenas admin pode usar (mesmos dados, mas dentro do grupo admin)

**Recomendação:** Use `/api/admin/statistics/my-stats` quando estiver em uma área administrativa para manter consistência com outras rotas admin.

---

## 3️⃣ Comparar Performance com Média dos Outros (Admin)

### Endpoint
```
GET /api/admin/statistics/compare-performance?period=month
```

### Quem pode usar
🔒 **Apenas Admin**

### O que retorna
Compara a performance do administrador logado com a média de todos os outros usuários do sistema.

### Exemplo de Requisição

```javascript
const comparePerformance = async (period = 'month') => {
  const token = localStorage.getItem('token');
  const response = await axios.get(`/api/admin/statistics/compare-performance?period=${period}`, {
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
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin"
  },
  "my_performance": {
    "productivity": {
      "tickets_assigned": 25,
      "tickets_closed": 20,
      "resolution_rate": 80.0,
      "response_rate": 90.0,
      "average_response_time_hours": 0.5,
      "average_resolution_time_hours": 2.0
    },
    "response_time": {
      "first_response": {
        "average_hours": 0.5
      }
    },
    "overview": {
      "total": 25,
      "resolvidos": 20
    }
  },
  "average_others": {
    "productivity": {
      "tickets_assigned": 18.5,
      "tickets_closed": 15.2,
      "resolution_rate": 75.5,
      "response_rate": 85.0,
      "average_response_time_hours": 0.8,
      "average_resolution_time_hours": 2.5
    },
    "response_time": {
      "first_response": {
        "average_hours": 0.8
      }
    },
    "overview": {
      "total": 18.5,
      "resolvidos": 15.2
    },
    "total_users": 10
  },
  "comparison": {
    "tickets_assigned": {
      "my_value": 25,
      "average_value": 18.5,
      "difference_percent": 35.14,
      "status": "better"
    },
    "tickets_closed": {
      "my_value": 20,
      "average_value": 15.2,
      "difference_percent": 31.58,
      "status": "better"
    },
    "resolution_rate": {
      "my_value": 80.0,
      "average_value": 75.5,
      "difference_percent": 5.96,
      "status": "similar"
    },
    "response_rate": {
      "my_value": 90.0,
      "average_value": 85.0,
      "difference_percent": 5.88,
      "status": "similar"
    },
    "average_response_time": {
      "my_value": 0.5,
      "average_value": 0.8,
      "difference_percent": -37.5,
      "status": "better"
    },
    "average_resolution_time": {
      "my_value": 2.0,
      "average_value": 2.5,
      "difference_percent": -20.0,
      "status": "better"
    },
    "first_response_time": {
      "my_value": 0.5,
      "average_value": 0.8,
      "difference_percent": -37.5,
      "status": "better"
    }
  }
}
```

### Campos de Comparação

Cada métrica na seção `comparison` contém:

- `my_value` - Seu valor pessoal
- `average_value` - Média dos outros usuários
- `difference_percent` - Diferença percentual (positivo = você está acima da média, negativo = abaixo)
- `status` - Status da comparação:
  - `"better"` - Você está significativamente melhor (>10% de diferença)
  - `"worse"` - Você está significativamente pior (>10% de diferença)
  - `"similar"` - Você está similar à média (±10%)

**Nota:** Para tempos (response_time, resolution_time), valores negativos são melhores (menor tempo = melhor).

### Exemplo de Uso no React

```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

const PerformanceComparison = () => {
  const [comparison, setComparison] = useState(null);
  const [loading, setLoading] = useState(true);
  const [period, setPeriod] = useState('month');

  useEffect(() => {
    const fetchComparison = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('token');
        const response = await axios.get(`/api/admin/statistics/compare-performance?period=${period}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        setComparison(response.data);
      } catch (error) {
        console.error('Erro ao carregar comparação:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchComparison();
  }, [period]);

  if (loading) return <div>Carregando...</div>;
  if (!comparison) return null;

  const getStatusColor = (status) => {
    switch (status) {
      case 'better': return 'text-green-600';
      case 'worse': return 'text-red-600';
      default: return 'text-gray-600';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'better': return '↑';
      case 'worse': return '↓';
      default: return '→';
    }
  };

  return (
    <div>
      <h1>Comparação de Performance</h1>
      
      <select value={period} onChange={(e) => setPeriod(e.target.value)}>
        <option value="day">Hoje</option>
        <option value="week">Esta Semana</option>
        <option value="month">Este Mês</option>
        <option value="year">Este Ano</option>
      </select>

      <div className="comparison-grid">
        {Object.entries(comparison.comparison).map(([key, metric]) => (
          <div key={key} className="comparison-card">
            <h3>{key.replace(/_/g, ' ').toUpperCase()}</h3>
            <div className="values">
              <div>
                <span className="label">Meu valor:</span>
                <span className="value">{metric.my_value}</span>
              </div>
              <div>
                <span className="label">Média dos outros:</span>
                <span className="value">{metric.average_value}</span>
              </div>
              <div>
                <span className="label">Diferença:</span>
                <span className={`difference ${getStatusColor(metric.status)}`}>
                  {getStatusIcon(metric.status)} {Math.abs(metric.difference_percent)}%
                </span>
              </div>
            </div>
            <div className={`status-badge ${metric.status}`}>
              {metric.status === 'better' ? 'Melhor' : 
               metric.status === 'worse' ? 'Pior' : 'Similar'}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default PerformanceComparison;
```

### Gráfico de Comparação

```javascript
// Exemplo com Chart.js
import { Bar } from 'react-chartjs-2';

const ComparisonChart = ({ comparison }) => {
  const metrics = Object.keys(comparison.comparison);
  
  const chartData = {
    labels: metrics.map(key => key.replace(/_/g, ' ')),
    datasets: [
      {
        label: 'Minha Performance',
        data: metrics.map(key => comparison.comparison[key].my_value),
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
      },
      {
        label: 'Média dos Outros',
        data: metrics.map(key => comparison.comparison[key].average_value),
        backgroundColor: 'rgba(255, 99, 132, 0.5)',
      }
    ]
  };

  return <Bar data={chartData} />;
};
```

---

## 4️⃣ Dashboard Geral (Admin)

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

## 5️⃣ Estatísticas de Tickets (Admin)

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

## 6️⃣ Estatísticas de Usuários (Admin)

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

## 7️⃣ Estatísticas de Mensagens (Admin)

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

## 8️⃣ Estatísticas de Anexos (Admin)

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

---

## 📋 Resumo das Rotas por Tipo de Usuário

### 👤 Qualquer Usuário Autenticado
| Rota | Descrição | Uso |
|------|-----------|-----|
| `GET /api/statistics/my-stats` | Estatísticas pessoais | Ver seus próprios tickets e performance |

### 🔒 Apenas Admin
| Rota | Descrição | Uso |
|------|-----------|-----|
| `GET /api/admin/statistics/my-stats` | Estatísticas pessoais do admin | Ver seus próprios dados (mesmo que acima, mas no grupo admin) |
| `GET /api/admin/statistics/compare-performance` | **🆕 Comparar performance** | Comparar sua performance com média dos outros |
| `GET /api/admin/statistics/dashboard` | Dashboard geral | Visão geral do sistema |
| `GET /api/admin/statistics/tickets` | Estatísticas de tickets | Análise detalhada de todos os tickets |
| `GET /api/admin/statistics/users` | Estatísticas de usuários | Performance e atividade dos usuários |
| `GET /api/admin/statistics/messages` | Estatísticas de mensagens | Análise de mensagens do sistema |
| `GET /api/admin/statistics/attachments` | Estatísticas de anexos | Uso e tamanho de anexos |

---

## 🎯 Casos de Uso Comuns

### Caso 1: Dashboard Pessoal (Qualquer Usuário)

```javascript
// Componente de dashboard pessoal
const PersonalDashboard = () => {
  const [period, setPeriod] = useState('month');
  const { data, loading } = useMyStats(period);

  if (loading) return <Spinner />;

  return (
    <div>
      <PeriodSelector value={period} onChange={setPeriod} />
      
      <StatsCards>
        <Card title="Total de Tickets" value={data.overview.total} />
        <Card title="Resolvidos" value={data.overview.resolvidos} />
        <Card title="Taxa de Resolução" value={`${data.productivity.resolution_rate}%`} />
        <Card title="Tempo Médio de Resposta" value={`${data.response_time.first_response.average_hours}h`} />
      </StatsCards>

      <ProductivityChart data={data.productivity} />
      <TicketsByDayChart data={data.by_day} />
    </div>
  );
};
```

### Caso 2: Comparação de Performance (Admin)

```javascript
// Componente de comparação para admin
const AdminPerformanceComparison = () => {
  const [period, setPeriod] = useState('month');
  const { comparison, loading } = usePerformanceComparison(period);

  if (loading) return <Spinner />;
  if (!comparison) return null;

  return (
    <div>
      <h1>Minha Performance vs Média dos Outros</h1>
      <PeriodSelector value={period} onChange={setPeriod} />
      
      <ComparisonTable>
        <thead>
          <tr>
            <th>Métrica</th>
            <th>Meu Valor</th>
            <th>Média dos Outros</th>
            <th>Diferença</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {Object.entries(comparison.comparison).map(([key, metric]) => (
            <tr key={key}>
              <td>{formatMetricName(key)}</td>
              <td>{metric.my_value}</td>
              <td>{metric.average_value}</td>
              <td>
                {metric.difference_percent > 0 ? '+' : ''}
                {metric.difference_percent}%
              </td>
              <td>
                <StatusBadge status={metric.status}>
                  {metric.status === 'better' && '🟢 Melhor'}
                  {metric.status === 'worse' && '🔴 Pior'}
                  {metric.status === 'similar' && '🟡 Similar'}
                </StatusBadge>
              </td>
            </tr>
          ))}
        </tbody>
      </ComparisonTable>

      <ComparisonChart 
        myData={comparison.my_performance}
        averageData={comparison.average_others}
      />
    </div>
  );
};
```

### Caso 3: Gráfico de Comparação (Chart.js)

```javascript
import { Bar } from 'react-chartjs-2';

const ComparisonBarChart = ({ comparison }) => {
  const metrics = [
    'tickets_assigned',
    'tickets_closed',
    'resolution_rate',
    'response_rate'
  ];

  const chartData = {
    labels: metrics.map(key => formatMetricName(key)),
    datasets: [
      {
        label: 'Minha Performance',
        data: metrics.map(key => comparison.comparison[key].my_value),
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1,
      },
      {
        label: 'Média dos Outros',
        data: metrics.map(key => comparison.comparison[key].average_value),
        backgroundColor: 'rgba(255, 99, 132, 0.6)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1,
      }
    ]
  };

  const options = {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true
      }
    }
  };

  return <Bar data={chartData} options={options} />;
};
```

### Caso 4: Cards de Comparação com Indicadores Visuais

```javascript
const ComparisonCard = ({ metric, comparison }) => {
  const { my_value, average_value, difference_percent, status } = comparison.comparison[metric];
  
  const getStatusColor = () => {
    switch (status) {
      case 'better': return '#10b981'; // green
      case 'worse': return '#ef4444'; // red
      default: return '#6b7280'; // gray
    }
  };

  const getStatusIcon = () => {
    switch (status) {
      case 'better': return '↑';
      case 'worse': return '↓';
      default: return '→';
    }
  };

  return (
    <div className="comparison-card">
      <h3>{formatMetricName(metric)}</h3>
      
      <div className="values-container">
        <div className="value-box">
          <span className="label">Meu valor</span>
          <span className="value my-value">{my_value}</span>
        </div>
        
        <div className="value-box">
          <span className="label">Média</span>
          <span className="value average-value">{average_value}</span>
        </div>
      </div>

      <div className="difference" style={{ color: getStatusColor() }}>
        <span className="icon">{getStatusIcon()}</span>
        <span className="percent">
          {Math.abs(difference_percent).toFixed(1)}%
        </span>
        <span className="status-text">
          {status === 'better' && 'Melhor que a média'}
          {status === 'worse' && 'Abaixo da média'}
          {status === 'similar' && 'Similar à média'}
        </span>
      </div>
    </div>
  );
};

// Uso
const ComparisonGrid = ({ comparison }) => {
  const metrics = Object.keys(comparison.comparison);
  
  return (
    <div className="comparison-grid">
      {metrics.map(metric => (
        <ComparisonCard 
          key={metric} 
          metric={metric} 
          comparison={comparison} 
        />
      ))}
    </div>
  );
};
```

---

## 🚀 Próximos Passos

1. ✅ Implementar componentes de visualização
2. ✅ Adicionar filtros interativos
3. ✅ Criar comparação de performance
4. ⏳ Criar exportação de relatórios (PDF/Excel)
5. ⏳ Implementar cache para melhor performance
6. ⏳ Adicionar comparação entre períodos
7. ⏳ Adicionar notificações quando performance melhorar/piorar

