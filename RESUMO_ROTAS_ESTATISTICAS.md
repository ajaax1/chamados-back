# 📊 Resumo das Rotas de Estatísticas - Frontend

## 🎯 Guia Rápido de Uso

Este documento fornece um resumo rápido de todas as rotas de estatísticas disponíveis e como utilizá-las no frontend.

---

## 📋 Tabela de Rotas

### 👤 Rotas Pessoais (Qualquer Usuário Autenticado)

| Rota | Método | Descrição | Exemplo de Uso |
|------|--------|-----------|----------------|
| `/api/statistics/my-stats` | GET | Suas próprias estatísticas | Ver seus tickets, produtividade e tempos |

### 🔒 Rotas Administrativas (Apenas Admin)

| Rota | Método | Descrição | Exemplo de Uso |
|------|--------|-----------|----------------|
| `/api/admin/statistics/my-stats` | GET | Estatísticas pessoais do admin | Ver seus próprios dados (mesmo que acima) |
| `/api/admin/statistics/compare-performance` | GET | **🆕 Comparar sua performance** | Comparar com média dos outros usuários |
| `/api/admin/statistics/dashboard` | GET | Dashboard geral do sistema | Visão geral completa |
| `/api/admin/statistics/tickets` | GET | Estatísticas detalhadas de tickets | Análise completa de todos os tickets |
| `/api/admin/statistics/users` | GET | Estatísticas de usuários | Performance e atividade dos usuários |
| `/api/admin/statistics/messages` | GET | Estatísticas de mensagens | Análise de mensagens |
| `/api/admin/statistics/attachments` | GET | Estatísticas de anexos | Uso e tamanho de anexos |

---

## 🚀 Exemplos de Código Prontos para Usar

### 1. Hook para Estatísticas Pessoais

```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

export const useMyStats = (period = 'month') => {
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
```

**Uso:**
```javascript
const MyComponent = () => {
  const { data, loading, error } = useMyStats('month');
  
  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;
  
  return (
    <div>
      <h2>Total: {data.overview.total}</h2>
      <p>Taxa de Resolução: {data.productivity.resolution_rate}%</p>
    </div>
  );
};
```

---

### 2. Hook para Comparação de Performance (Admin)

```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

export const usePerformanceComparison = (period = 'month') => {
  const [comparison, setComparison] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchComparison = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('token');
        const response = await axios.get(
          `/api/admin/statistics/compare-performance?period=${period}`,
          {
            headers: { 'Authorization': `Bearer ${token}` }
          }
        );
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
```

**Uso:**
```javascript
const ComparisonComponent = () => {
  const { comparison, loading, error } = usePerformanceComparison('month');
  
  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!comparison) return null;

  return (
    <div>
      <h2>Comparação de Performance</h2>
      {Object.entries(comparison.comparison).map(([key, metric]) => (
        <div key={key}>
          <h3>{key}</h3>
          <p>Meu valor: {metric.my_value}</p>
          <p>Média: {metric.average_value}</p>
          <p>Status: {metric.status}</p>
        </div>
      ))}
    </div>
  );
};
```

---

### 3. Serviço Centralizado (Recomendado)

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
    const response = await axios.get(
      `${API_URL}/statistics/my-stats?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },

  // Estatísticas pessoais do admin
  getAdminMyStats: async (period = 'month') => {
    const response = await axios.get(
      `${API_URL}/admin/statistics/my-stats?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },

  // 🆕 Comparar performance (admin)
  comparePerformance: async (period = 'month') => {
    const response = await axios.get(
      `${API_URL}/admin/statistics/compare-performance?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },

  // Dashboard geral (admin)
  getDashboard: async (period = 'month') => {
    const response = await axios.get(
      `${API_URL}/admin/statistics/dashboard?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },

  // Estatísticas de tickets (admin)
  getTicketsStats: async (period = 'month') => {
    const response = await axios.get(
      `${API_URL}/admin/statistics/tickets?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },

  // Estatísticas de usuários (admin)
  getUsersStats: async (period = 'month') => {
    const response = await axios.get(
      `${API_URL}/admin/statistics/users?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },

  // Estatísticas de mensagens (admin)
  getMessagesStats: async (period = 'month') => {
    const response = await axios.get(
      `${API_URL}/admin/statistics/messages?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },

  // Estatísticas de anexos (admin)
  getAttachmentsStats: async (period = 'month') => {
    const response = await axios.get(
      `${API_URL}/admin/statistics/attachments?period=${period}`,
      { headers: getAuthHeaders() }
    );
    return response.data;
  },
};
```

**Uso:**
```javascript
import { statisticsService } from './services/statisticsService';

// Em um componente
const loadData = async () => {
  try {
    // Minhas estatísticas
    const myStats = await statisticsService.getMyStats('month');
    console.log('Meus tickets:', myStats.overview.total);

    // Se for admin, comparar performance
    if (user.role === 'admin') {
      const comparison = await statisticsService.comparePerformance('month');
      console.log('Comparação:', comparison.comparison);
    }
  } catch (error) {
    console.error('Erro:', error);
  }
};
```

---

## 📊 Estrutura de Dados Retornados

### Estatísticas Pessoais (`/api/statistics/my-stats`)

```typescript
{
  period: string;
  start_date: string;
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
  };
  overview: {
    total: number;
    abertos: number;
    pendentes: number;
    resolvidos: number;
    finalizados: number;
  };
  productivity: {
    tickets_assigned: number;
    tickets_closed: number;
    resolution_rate: number;
    response_rate: number;
    average_response_time_hours: number;
    average_resolution_time_hours: number;
  };
  response_time: {
    first_response: {
      average_hours: number;
      tickets_with_response: number;
    };
    resolution_time: {
      average_hours: number;
      resolved_tickets: number;
    };
  };
}
```

### Comparação de Performance (`/api/admin/statistics/compare-performance`)

```typescript
{
  period: string;
  start_date: string;
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
  };
  my_performance: {
    productivity: { ... };
    response_time: { ... };
    overview: { ... };
  };
  average_others: {
    productivity: { ... };
    response_time: { ... };
    overview: { ... };
    total_users: number;
  };
  comparison: {
    tickets_assigned: {
      my_value: number;
      average_value: number;
      difference_percent: number;
      status: "better" | "worse" | "similar";
    };
    tickets_closed: { ... };
    resolution_rate: { ... };
    response_rate: { ... };
    average_response_time: { ... };
    average_resolution_time: { ... };
    first_response_time: { ... };
  };
}
```

---

## 🎨 Componente de Comparação Completo

```javascript
import React from 'react';
import { usePerformanceComparison } from '../hooks/usePerformanceComparison';

const PerformanceComparison = () => {
  const [period, setPeriod] = React.useState('month');
  const { comparison, loading, error } = usePerformanceComparison(period);

  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;
  if (!comparison) return null;

  const getStatusColor = (status) => {
    switch (status) {
      case 'better': return 'text-green-600 bg-green-50';
      case 'worse': return 'text-red-600 bg-red-50';
      default: return 'text-gray-600 bg-gray-50';
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
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-4">Comparação de Performance</h1>
      
      <select 
        value={period} 
        onChange={(e) => setPeriod(e.target.value)}
        className="mb-4 p-2 border rounded"
      >
        <option value="day">Hoje</option>
        <option value="week">Esta Semana</option>
        <option value="month">Este Mês</option>
        <option value="year">Este Ano</option>
      </select>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {Object.entries(comparison.comparison).map(([key, metric]) => (
          <div key={key} className="border rounded-lg p-4">
            <h3 className="font-semibold mb-2 capitalize">
              {key.replace(/_/g, ' ')}
            </h3>
            
            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-gray-600">Meu valor:</span>
                <span className="font-bold">{metric.my_value}</span>
              </div>
              
              <div className="flex justify-between">
                <span className="text-gray-600">Média:</span>
                <span className="font-bold">{metric.average_value}</span>
              </div>
              
              <div className={`mt-3 p-2 rounded ${getStatusColor(metric.status)}`}>
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium">
                    {getStatusIcon(metric.status)} {Math.abs(metric.difference_percent).toFixed(1)}%
                  </span>
                  <span className="text-xs">
                    {metric.status === 'better' && 'Melhor'}
                    {metric.status === 'worse' && 'Pior'}
                    {metric.status === 'similar' && 'Similar'}
                  </span>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default PerformanceComparison;
```

---

## 📝 Parâmetros de Período

Todas as rotas suportam o parâmetro `period`:

- `day` - Hoje
- `week` - Esta semana
- `month` - Este mês (padrão)
- `year` - Este ano
- `all` - Todos os dados

**Exemplo:**
```javascript
// Buscar dados da semana
const stats = await statisticsService.getMyStats('week');

// Buscar comparação do ano
const comparison = await statisticsService.comparePerformance('year');
```

---

## ⚠️ Tratamento de Erros

```javascript
try {
  const stats = await statisticsService.getMyStats('month');
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
}
```

---

## 🔗 Links Úteis

- **Guia Completo:** `GUIA_ESTATISTICAS_FRONTEND.md`
- **Documentação da API:** `ESTATISTICAS_FRONTEND.md`

---

## ✅ Checklist de Implementação

- [ ] Criar serviço de estatísticas (`statisticsService.js`)
- [ ] Criar hooks personalizados (`useMyStats`, `usePerformanceComparison`)
- [ ] Implementar componente de estatísticas pessoais
- [ ] Implementar componente de comparação de performance (admin)
- [ ] Adicionar tratamento de erros
- [ ] Adicionar loading states
- [ ] Implementar filtros de período
- [ ] Criar gráficos de visualização
- [ ] Adicionar testes

---

**Última atualização:** Novembro 2025

