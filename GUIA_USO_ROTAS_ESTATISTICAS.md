# 📚 Guia Prático: Como Usar as Rotas de Estatísticas

Guia completo e prático de como utilizar as rotas de estatísticas no seu frontend.

---

## 🔧 Configuração Inicial

### 1. Configurar Axios com Autenticação

```javascript
// services/api.js
import axios from 'axios';

const API_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Adicionar token automaticamente em todas as requisições
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Tratar erros de autenticação
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

---

## 👤 ROTAS PESSOAIS (Qualquer Usuário Autenticado)

### 1. Estatísticas e Métricas

**Rota:** `GET /api/statistics/my-stats?period=month`

#### O que retorna:
- Overview (total, abertos, pendentes, resolvidos)
- Produtividade (tickets atribuídos, fechados, taxas)
- Tempos de resposta
- Tickets por origem
- Criados e fechados por período

#### Exemplo de Uso:

```javascript
// services/statisticsService.js
import api from './api';

export const getMyStats = async (period = 'month') => {
  try {
    const response = await api.get(`/statistics/my-stats?period=${period}`);
    return response.data;
  } catch (error) {
    console.error('Erro ao buscar estatísticas:', error);
    throw error;
  }
};
```

#### Hook React:

```javascript
// hooks/useMyStats.js
import { useState, useEffect } from 'react';
import { getMyStats } from '../services/statisticsService';

export const useMyStats = (period = 'month') => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        setLoading(true);
        setError(null);
        const stats = await getMyStats(period);
        setData(stats);
      } catch (err) {
        setError(err.message || 'Erro ao carregar estatísticas');
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [period]);

  return { data, loading, error };
};
```

#### Componente React Completo:

```javascript
// components/MyStatistics.jsx
import React, { useState } from 'react';
import { useMyStats } from '../hooks/useMyStats';

const MyStatistics = () => {
  const [period, setPeriod] = useState('month');
  const { data, loading, error } = useMyStats(period);

  if (loading) return <div>Carregando estatísticas...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!data) return null;

  return (
    <div className="my-statistics">
      <h1>Minhas Estatísticas</h1>
      
      {/* Seletor de Período */}
      <div className="period-selector">
        <label>Período: </label>
        <select value={period} onChange={(e) => setPeriod(e.target.value)}>
          <option value="day">Hoje</option>
          <option value="week">Esta Semana</option>
          <option value="month">Este Mês</option>
          <option value="year">Este Ano</option>
        </select>
      </div>

      {/* Cards de Resumo */}
      <div className="stats-grid">
        <div className="stat-card">
          <h3>Total de Tickets</h3>
          <p className="big-number">{data.overview.total}</p>
        </div>
        
        <div className="stat-card">
          <h3>Resolvidos</h3>
          <p className="big-number">{data.overview.resolvidos}</p>
        </div>
        
        <div className="stat-card">
          <h3>Taxa de Resolução</h3>
          <p className="big-number">{data.productivity.resolution_rate}%</p>
        </div>
        
        <div className="stat-card">
          <h3>Tempo Médio de Resposta</h3>
          <p className="big-number">
            {data.response_time.first_response.average_hours.toFixed(2)}h
          </p>
        </div>
      </div>

      {/* Seção de Produtividade */}
      <div className="productivity-section">
        <h2>Produtividade</h2>
        <div className="productivity-grid">
          <div>
            <span className="label">Tickets Atribuídos:</span>
            <span className="value">{data.productivity.tickets_assigned}</span>
          </div>
          <div>
            <span className="label">Tickets Fechados:</span>
            <span className="value">{data.productivity.tickets_closed}</span>
          </div>
          <div>
            <span className="label">Tickets Respondidos:</span>
            <span className="value">{data.productivity.tickets_responded}</span>
          </div>
          <div>
            <span className="label">Taxa de Resposta:</span>
            <span className="value">{data.productivity.response_rate}%</span>
          </div>
        </div>
      </div>

      {/* Gráfico de Tickets por Dia */}
      <div className="chart-section">
        <h2>Tickets por Dia</h2>
        <div className="tickets-by-day">
          {data.by_day.map((day) => (
            <div key={day.date} className="day-item">
              <span className="date">{new Date(day.date).toLocaleDateString('pt-BR')}</span>
              <span className="count">{day.total} tickets</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default MyStatistics;
```

#### Estrutura dos Dados Retornados:

```javascript
{
  period: "month",
  start_date: "2025-11-01 00:00:00",
  user: {
    id: 1,
    name: "João Silva",
    email: "joao@example.com",
    role: "support"
  },
  overview: {
    total: 25,
    abertos: 5,
    pendentes: 3,
    resolvidos: 15,
    finalizados: 2,
    alta_prioridade: 8,
    media_prioridade: 12,
    baixa_prioridade: 5
  },
  productivity: {
    tickets_assigned: 25,
    tickets_closed: 17,
    tickets_not_resolved: 8,
    tickets_responded: 22,
    resolution_rate: 68.0,
    response_rate: 88.0,
    average_response_time_minutes: 30.5,
    average_response_time_hours: 0.51,
    average_resolution_time_minutes: 120.5,
    average_resolution_time_hours: 2.01
  },
  response_time: {
    first_response: {
      average_minutes: 45.5,
      average_hours: 0.76,
      tickets_with_response: 20,
      total_tickets: 25
    },
    resolution_time: {
      average_minutes: 180.5,
      average_hours: 3.01,
      resolved_tickets: 17
    },
    total_open_time: {
      average_minutes: 240.5,
      average_hours: 4.01,
      average_days: 0.17
    }
  },
  tickets_by_origin: {
    total: 25,
    by_origin: {
      formulario_web: { total: 10, percentage: 40.0 },
      email: { total: 8, percentage: 32.0 },
      api: { total: 5, percentage: 20.0 },
      tel_manual: { total: 2, percentage: 8.0 }
    }
  },
  by_status: {
    aberto: 5,
    pendente: 3,
    resolvido: 15,
    finalizado: 2
  },
  by_priority: {
    alta: 8,
    média: 12,
    baixa: 5
  },
  by_day: [
    { date: "2025-11-01", total: 3 },
    { date: "2025-11-02", total: 5 }
  ],
  tickets_created_by_period: [
    { period: "2025-11-01", total: 3 }
  ],
  tickets_closed_by_period: [
    { period: "2025-11-01", created: 3, closed: 2, open: 1 }
  ]
}
```

---

### 2. Histórico de Atividades

**Rota:** `GET /api/statistics/my-activity?period=month&limit=50`

#### O que retorna:
- Summary (resumo de atividades)
- Timeline (lista cronológica de ações)
- Listas detalhadas (tickets criados, atualizados, mensagens, anexos)

#### Exemplo de Uso:

```javascript
// services/statisticsService.js
export const getMyActivity = async (period = 'month', limit = 50) => {
  try {
    const response = await api.get(`/statistics/my-activity?period=${period}&limit=${limit}`);
    return response.data;
  } catch (error) {
    console.error('Erro ao buscar atividades:', error);
    throw error;
  }
};
```

#### Hook React:

```javascript
// hooks/useMyActivity.js
import { useState, useEffect } from 'react';
import { getMyActivity } from '../services/statisticsService';

export const useMyActivity = (period = 'month', limit = 50) => {
  const [activity, setActivity] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchActivity = async () => {
      try {
        setLoading(true);
        setError(null);
        const data = await getMyActivity(period, limit);
        setActivity(data);
      } catch (err) {
        setError(err.message || 'Erro ao carregar atividades');
      } finally {
        setLoading(false);
      }
    };

    fetchActivity();
  }, [period, limit]);

  return { activity, loading, error };
};
```

#### Componente React Completo:

```javascript
// components/MyActivity.jsx
import React, { useState } from 'react';
import { useMyActivity } from '../hooks/useMyActivity';

const MyActivity = () => {
  const [period, setPeriod] = useState('month');
  const [limit, setLimit] = useState(50);
  const { activity, loading, error } = useMyActivity(period, limit);

  if (loading) return <div>Carregando atividades...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!activity) return null;

  const getActivityIcon = (type) => {
    switch (type) {
      case 'ticket_created': return '🎫';
      case 'ticket_updated': return '✏️';
      case 'message_sent': return '💬';
      case 'attachment_uploaded': return '📎';
      default: return '📋';
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('pt-BR');
  };

  return (
    <div className="my-activity">
      <h1>Minhas Atividades</h1>
      
      {/* Filtros */}
      <div className="filters">
        <select value={period} onChange={(e) => setPeriod(e.target.value)}>
          <option value="day">Hoje</option>
          <option value="week">Esta Semana</option>
          <option value="month">Este Mês</option>
          <option value="year">Este Ano</option>
        </select>
        
        <select value={limit} onChange={(e) => setLimit(Number(e.target.value))}>
          <option value={25}>25 atividades</option>
          <option value={50}>50 atividades</option>
          <option value={100}>100 atividades</option>
        </select>
      </div>

      {/* Resumo */}
      <div className="activity-summary">
        <div className="summary-card">
          <h3>Tickets Criados</h3>
          <p>{activity.summary.tickets_created}</p>
        </div>
        <div className="summary-card">
          <h3>Tickets Atualizados</h3>
          <p>{activity.summary.tickets_updated}</p>
        </div>
        <div className="summary-card">
          <h3>Mensagens Enviadas</h3>
          <p>{activity.summary.messages_sent}</p>
        </div>
        <div className="summary-card">
          <h3>Anexos Enviados</h3>
          <p>{activity.summary.attachments_uploaded}</p>
        </div>
      </div>

      {/* Timeline */}
      <div className="activity-timeline">
        <h2>Timeline de Atividades</h2>
        {activity.timeline.map((item, index) => (
          <div key={index} className="timeline-item">
            <div className="timeline-icon">{getActivityIcon(item.type)}</div>
            <div className="timeline-content">
              <h4>{item.description}</h4>
              {item.ticket_title && (
                <p className="ticket-link">Ticket: {item.ticket_title}</p>
              )}
              {item.message_preview && (
                <p className="message-preview">{item.message_preview}</p>
              )}
              <span className="timeline-date">{formatDate(item.created_at)}</span>
            </div>
          </div>
        ))}
      </div>

      {/* Lista de Tickets Criados */}
      <div className="tickets-created">
        <h2>Tickets Criados ({activity.tickets_created.length})</h2>
        <div className="tickets-list">
          {activity.tickets_created.map((ticket) => (
            <div key={ticket.id} className="ticket-item">
              <h4>{ticket.title}</h4>
              <p>Status: {ticket.status} | Prioridade: {ticket.priority}</p>
              <p>Criado em: {formatDate(ticket.created_at)}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Lista de Mensagens */}
      <div className="messages-sent">
        <h2>Mensagens Enviadas ({activity.messages_sent.length})</h2>
        <div className="messages-list">
          {activity.messages_sent.map((message) => (
            <div key={message.id} className="message-item">
              <p><strong>Ticket:</strong> {message.ticket_title || 'N/A'}</p>
              <p className="message-text">{message.message}</p>
              <p className="message-meta">
                {message.is_internal ? '🔒 Interna' : '🌐 Externa'} | 
                {formatDate(message.created_at)}
              </p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default MyActivity;
```

#### Estrutura dos Dados Retornados:

```javascript
{
  period: "month",
  start_date: "2025-11-01 00:00:00",
  user: {
    id: 1,
    name: "João Silva",
    email: "joao@example.com",
    role: "support"
  },
  summary: {
    tickets_created: 15,
    tickets_updated: 12,
    messages_sent: 45,
    attachments_uploaded: 8
  },
  timeline: [
    {
      type: "ticket_created",
      id: 123,
      title: "Problema no sistema",
      description: "Ticket criado: Problema no sistema",
      status: "aberto",
      priority: "alta",
      created_at: "2025-11-15T10:30:00.000000Z"
    },
    {
      type: "message_sent",
      id: 456,
      ticket_id: 123,
      ticket_title: "Problema no sistema",
      description: "Mensagem enviada",
      message_preview: "Olá, vou analisar o problema...",
      is_internal: false,
      created_at: "2025-11-15T10:35:00.000000Z"
    }
  ],
  tickets_created: [ /* lista de tickets */ ],
  tickets_updated: [ /* lista de tickets */ ],
  messages_sent: [ /* lista de mensagens */ ],
  attachments_uploaded: [ /* lista de anexos */ ]
}
```

---

## 🔒 ROTAS ADMINISTRATIVAS (Apenas Admin)

### 3. Estatísticas Pessoais do Admin

**Rota:** `GET /api/admin/statistics/my-stats?period=month`

**Mesma estrutura da rota `/api/statistics/my-stats`**, mas dentro do grupo admin.

#### Exemplo de Uso:

```javascript
// services/statisticsService.js
export const getAdminMyStats = async (period = 'month') => {
  try {
    const response = await api.get(`/admin/statistics/my-stats?period=${period}`);
    return response.data;
  } catch (error) {
    console.error('Erro ao buscar estatísticas do admin:', error);
    throw error;
  }
};
```

**Uso idêntico à rota `/api/statistics/my-stats`**, apenas muda a URL.

---

### 4. Comparar Performance

**Rota:** `GET /api/admin/statistics/compare-performance?period=month`

#### O que retorna:
- Sua performance pessoal
- Média dos outros usuários
- Comparação detalhada com status (better/worse/similar)

#### Exemplo de Uso:

```javascript
// services/statisticsService.js
export const comparePerformance = async (period = 'month') => {
  try {
    const response = await api.get(`/admin/statistics/compare-performance?period=${period}`);
    return response.data;
  } catch (error) {
    console.error('Erro ao comparar performance:', error);
    throw error;
  }
};
```

#### Hook React:

```javascript
// hooks/usePerformanceComparison.js
import { useState, useEffect } from 'react';
import { comparePerformance } from '../services/statisticsService';

export const usePerformanceComparison = (period = 'month') => {
  const [comparison, setComparison] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchComparison = async () => {
      try {
        setLoading(true);
        setError(null);
        const data = await comparePerformance(period);
        setComparison(data);
      } catch (err) {
        setError(err.message || 'Erro ao carregar comparação');
      } finally {
        setLoading(false);
      }
    };

    fetchComparison();
  }, [period]);

  return { comparison, loading, error };
};
```

#### Componente React Completo:

```javascript
// components/PerformanceComparison.jsx
import React, { useState } from 'react';
import { usePerformanceComparison } from '../hooks/usePerformanceComparison';

const PerformanceComparison = () => {
  const [period, setPeriod] = useState('month');
  const { comparison, loading, error } = usePerformanceComparison(period);

  if (loading) return <div>Carregando comparação...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!comparison) return null;

  const getStatusConfig = (status) => {
    switch (status) {
      case 'better':
        return {
          bg: 'bg-green-50',
          text: 'text-green-800',
          border: 'border-green-200',
          icon: '↑',
          label: 'Melhor'
        };
      case 'worse':
        return {
          bg: 'bg-red-50',
          text: 'text-red-800',
          border: 'border-red-200',
          icon: '↓',
          label: 'Pior'
        };
      default:
        return {
          bg: 'bg-gray-50',
          text: 'text-gray-800',
          border: 'border-gray-200',
          icon: '→',
          label: 'Similar'
        };
    }
  };

  const formatMetricName = (key) => {
    const names = {
      tickets_assigned: 'Tickets Atribuídos',
      tickets_closed: 'Tickets Fechados',
      resolution_rate: 'Taxa de Resolução',
      response_rate: 'Taxa de Resposta',
      average_response_time: 'Tempo Médio de Resposta',
      average_resolution_time: 'Tempo Médio de Resolução',
      first_response_time: 'Tempo de Primeira Resposta'
    };
    return names[key] || key.replace(/_/g, ' ');
  };

  return (
    <div className="performance-comparison">
      <h1>Comparação de Performance</h1>
      
      <div className="period-selector">
        <label>Período: </label>
        <select value={period} onChange={(e) => setPeriod(e.target.value)}>
          <option value="day">Hoje</option>
          <option value="week">Esta Semana</option>
          <option value="month">Este Mês</option>
          <option value="year">Este Ano</option>
        </select>
      </div>

      {/* Informações do Usuário */}
      <div className="user-info">
        <h2>Comparando: {comparison.user.name}</h2>
        <p>Média calculada com {comparison.average_others.total_users} outros usuários</p>
      </div>

      {/* Grid de Comparação */}
      <div className="comparison-grid">
        {Object.entries(comparison.comparison).map(([key, metric]) => {
          const statusConfig = getStatusConfig(metric.status);
          const isTimeMetric = key.includes('time');
          
          return (
            <div 
              key={key} 
              className={`comparison-card ${statusConfig.bg} ${statusConfig.border}`}
            >
              <h3>{formatMetricName(key)}</h3>
              
              <div className="comparison-values">
                <div className="value-row">
                  <span className="label">Meu valor:</span>
                  <span className="value my-value">{metric.my_value}</span>
                </div>
                
                <div className="value-row">
                  <span className="label">Média dos outros:</span>
                  <span className="value avg-value">{metric.average_value}</span>
                </div>
                
                <div className={`difference ${statusConfig.text}`}>
                  <span className="icon">{statusConfig.icon}</span>
                  <span className="percent">
                    {metric.difference_percent > 0 ? '+' : ''}
                    {metric.difference_percent.toFixed(1)}%
                  </span>
                </div>
              </div>

              <div className={`status-badge ${statusConfig.bg} ${statusConfig.text}`}>
                {statusConfig.label}
                {isTimeMetric && metric.status === 'better' && ' (menor tempo)'}
                {isTimeMetric && metric.status === 'worse' && ' (maior tempo)'}
              </div>
            </div>
          );
        })}
      </div>

      {/* Resumo Visual */}
      <div className="comparison-summary">
        <h2>Resumo</h2>
        <div className="summary-stats">
          <div className="summary-item">
            <span className="label">Métricas Melhores:</span>
            <span className="value green">
              {Object.values(comparison.comparison).filter(m => m.status === 'better').length}
            </span>
          </div>
          <div className="summary-item">
            <span className="label">Métricas Similares:</span>
            <span className="value gray">
              {Object.values(comparison.comparison).filter(m => m.status === 'similar').length}
            </span>
          </div>
          <div className="summary-item">
            <span className="label">Métricas Piores:</span>
            <span className="value red">
              {Object.values(comparison.comparison).filter(m => m.status === 'worse').length}
            </span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PerformanceComparison;
```

#### Estrutura dos Dados Retornados:

```javascript
{
  period: "month",
  start_date: "2025-11-01 00:00:00",
  user: {
    id: 1,
    name: "Admin",
    email: "admin@example.com",
    role: "admin"
  },
  my_performance: {
    productivity: {
      tickets_assigned: 25,
      tickets_closed: 20,
      resolution_rate: 80.0,
      response_rate: 90.0,
      average_response_time_hours: 0.5,
      average_resolution_time_hours: 2.0
    },
    response_time: {
      first_response: {
        average_hours: 0.5
      }
    },
    overview: {
      total: 25,
      resolvidos: 20
    }
  },
  average_others: {
    productivity: {
      tickets_assigned: 18.5,
      tickets_closed: 15.2,
      resolution_rate: 75.5,
      response_rate: 85.0,
      average_response_time_hours: 0.8,
      average_resolution_time_hours: 2.5
    },
    response_time: {
      first_response: {
        average_hours: 0.8
      }
    },
    overview: {
      total: 18.5,
      resolvidos: 15.2
    },
    total_users: 10
  },
  comparison: {
    tickets_assigned: {
      my_value: 25,
      average_value: 18.5,
      difference_percent: 35.14,
      status: "better"
    },
    tickets_closed: {
      my_value: 20,
      average_value: 15.2,
      difference_percent: 31.58,
      status: "better"
    },
    resolution_rate: {
      my_value: 80.0,
      average_value: 75.5,
      difference_percent: 5.96,
      status: "similar"
    },
    response_rate: {
      my_value: 90.0,
      average_value: 85.0,
      difference_percent: 5.88,
      status: "similar"
    },
    average_response_time: {
      my_value: 0.5,
      average_value: 0.8,
      difference_percent: -37.5,
      status: "better"  // Menor tempo = melhor
    },
    average_resolution_time: {
      my_value: 2.0,
      average_value: 2.5,
      difference_percent: -20.0,
      status: "better"  // Menor tempo = melhor
    },
    first_response_time: {
      my_value: 0.5,
      average_value: 0.8,
      difference_percent: -37.5,
      status: "better"  // Menor tempo = melhor
    }
  }
}
```

---

## 🎯 Serviço Completo (Todas as Rotas)

```javascript
// services/statisticsService.js
import api from './api';

export const statisticsService = {
  // ========== ROTAS PESSOAIS ==========
  
  /**
   * Estatísticas e métricas pessoais
   * GET /api/statistics/my-stats
   */
  getMyStats: async (period = 'month') => {
    const response = await api.get(`/statistics/my-stats?period=${period}`);
    return response.data;
  },

  /**
   * Histórico de atividades
   * GET /api/statistics/my-activity
   */
  getMyActivity: async (period = 'month', limit = 50) => {
    const response = await api.get(`/statistics/my-activity?period=${period}&limit=${limit}`);
    return response.data;
  },

  // ========== ROTAS ADMINISTRATIVAS ==========

  /**
   * Estatísticas pessoais do admin
   * GET /api/admin/statistics/my-stats
   */
  getAdminMyStats: async (period = 'month') => {
    const response = await api.get(`/admin/statistics/my-stats?period=${period}`);
    return response.data;
  },

  /**
   * Comparar performance com média dos outros
   * GET /api/admin/statistics/compare-performance
   */
  comparePerformance: async (period = 'month') => {
    const response = await api.get(`/admin/statistics/compare-performance?period=${period}`);
    return response.data;
  },
};
```

---

## 📱 Exemplo de Página Completa

```javascript
// pages/StatisticsPage.jsx
import React, { useState, useEffect } from 'react';
import { statisticsService } from '../services/statisticsService';
import MyStatistics from '../components/MyStatistics';
import MyActivity from '../components/MyActivity';
import PerformanceComparison from '../components/PerformanceComparison';

const StatisticsPage = () => {
  const [period, setPeriod] = useState('month');
  const [activeTab, setActiveTab] = useState('stats');
  const user = JSON.parse(localStorage.getItem('user'));

  return (
    <div className="statistics-page">
      <h1>Estatísticas</h1>
      
      {/* Tabs */}
      <div className="tabs">
        <button 
          className={activeTab === 'stats' ? 'active' : ''}
          onClick={() => setActiveTab('stats')}
        >
          Minhas Estatísticas
        </button>
        <button 
          className={activeTab === 'activity' ? 'active' : ''}
          onClick={() => setActiveTab('activity')}
        >
          Minhas Atividades
        </button>
        {user?.role === 'admin' && (
          <button 
            className={activeTab === 'comparison' ? 'active' : ''}
            onClick={() => setActiveTab('comparison')}
          >
            Comparar Performance
          </button>
        )}
      </div>

      {/* Conteúdo */}
      <div className="tab-content">
        {activeTab === 'stats' && <MyStatistics />}
        {activeTab === 'activity' && <MyActivity />}
        {user?.role === 'admin' && activeTab === 'comparison' && (
          <PerformanceComparison />
        )}
      </div>
    </div>
  );
};

export default StatisticsPage;
```

---

## 🎨 Exemplo com Gráficos (Chart.js)

```javascript
// components/ProductivityChart.jsx
import { Bar } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

const ProductivityChart = ({ productivity }) => {
  const chartData = {
    labels: ['Tickets Atribuídos', 'Tickets Fechados', 'Tickets Respondidos'],
    datasets: [
      {
        label: 'Quantidade',
        data: [
          productivity.tickets_assigned,
          productivity.tickets_closed,
          productivity.tickets_responded
        ],
        backgroundColor: [
          'rgba(54, 162, 235, 0.5)',
          'rgba(75, 192, 192, 0.5)',
          'rgba(153, 102, 255, 0.5)'
        ],
        borderColor: [
          'rgba(54, 162, 235, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)'
        ],
        borderWidth: 1
      }
    ]
  };

  const options = {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Produtividade'
      }
    }
  };

  return <Bar data={chartData} options={options} />;
};

export default ProductivityChart;
```

---

## ⚠️ Tratamento de Erros Completo

```javascript
// utils/errorHandler.js
export const handleStatisticsError = (error) => {
  if (error.response) {
    switch (error.response.status) {
      case 401:
        // Token inválido ou expirado
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
        return 'Sessão expirada. Faça login novamente.';
      
      case 403:
        // Sem permissão (não é admin)
        return 'Acesso negado. Apenas administradores podem acessar esta rota.';
      
      case 404:
        return 'Rota não encontrada.';
      
      case 500:
        return 'Erro no servidor. Tente novamente mais tarde.';
      
      default:
        return error.response.data?.message || 'Erro ao carregar dados.';
    }
  } else if (error.request) {
    return 'Sem resposta do servidor. Verifique sua conexão.';
  } else {
    return error.message || 'Erro desconhecido.';
  }
};

// Uso
try {
  const stats = await statisticsService.getMyStats('month');
} catch (error) {
  const errorMessage = handleStatisticsError(error);
  alert(errorMessage);
  console.error('Erro detalhado:', error);
}
```

---

## 📝 Resumo Rápido

### Para Qualquer Usuário:

```javascript
// 1. Estatísticas
const stats = await statisticsService.getMyStats('month');
console.log('Total:', stats.overview.total);
console.log('Taxa de resolução:', stats.productivity.resolution_rate);

// 2. Atividades
const activity = await statisticsService.getMyActivity('month', 50);
console.log('Tickets criados:', activity.summary.tickets_created);
console.log('Timeline:', activity.timeline);
```

### Para Admin:

```javascript
// 3. Estatísticas pessoais do admin
const adminStats = await statisticsService.getAdminMyStats('month');

// 4. Comparar performance
const comparison = await statisticsService.comparePerformance('month');
console.log('Minha taxa:', comparison.comparison.resolution_rate.my_value);
console.log('Média:', comparison.comparison.resolution_rate.average_value);
console.log('Status:', comparison.comparison.resolution_rate.status);
```

---

## ✅ Checklist de Implementação

- [ ] Configurar axios com autenticação
- [ ] Criar `statisticsService.js`
- [ ] Criar hooks (`useMyStats`, `useMyActivity`, `usePerformanceComparison`)
- [ ] Criar componentes de visualização
- [ ] Adicionar tratamento de erros
- [ ] Adicionar loading states
- [ ] Implementar filtros de período
- [ ] Criar gráficos (opcional)
- [ ] Testar todas as rotas

---

**Pronto para usar!** Copie e cole os exemplos acima no seu projeto frontend.

