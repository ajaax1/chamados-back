# 📊 API de Estatísticas - Sistema de Chamados

## Visão Geral

Este documento descreve todas as rotas de estatísticas disponíveis para administradores do sistema. Todas as rotas requerem autenticação via Sanctum e permissão de administrador.

**Base URL:** `/api/admin/statistics`

**Autenticação:** Bearer Token (Sanctum)

**Permissão:** Apenas usuários com role `admin` podem acessar estas rotas.

---

## 📋 Índice de Rotas

1. [Dashboard Geral](#1-dashboard-geral)
2. [Estatísticas de Tickets](#2-estatísticas-de-tickets)
3. [Estatísticas de Usuários](#3-estatísticas-de-usuários)
4. [Estatísticas de Mensagens](#4-estatísticas-de-mensagens)
5. [Estatísticas de Anexos](#5-estatísticas-de-anexos)
6. [Tendências](#6-tendências)

---

## 1. Dashboard Geral

Retorna uma visão geral completa do sistema com todas as métricas principais.

### Endpoint
```
GET /api/admin/statistics/dashboard
```

### Parâmetros de Query (Opcionais)
- `period` (string): Período de análise
  - Valores: `day`, `week`, `month`, `year`, `all`
  - Padrão: `month`

### Exemplo de Requisição
```bash
GET /api/admin/statistics/dashboard?period=month
Authorization: Bearer {token}
```

### Resposta de Sucesso (200)
```json
{
  "period": "month",
  "start_date": "2025-11-01 00:00:00",
  "tickets": {
    "total": 150,
    "abertos": 25,
    "pendentes": 30,
    "resolvidos": 70,
    "finalizados": 25,
    "alta_prioridade": 15,
    "media_prioridade": 100,
    "baixa_prioridade": 35
  },
  "users": {
    "total": 50,
    "admins": 3,
    "support": 10,
    "assistant": 15,
    "cliente": 22
  },
  "messages": {
    "total": 450,
    "internal": 200,
    "external": 250
  },
  "performance": {
    "total_tickets": 150,
    "resolved_tickets": 95,
    "resolution_rate": 63.33,
    "pending_tickets": 30
  },
  "recent_activity": {
    "recent_tickets": [
      {
        "id": 123,
        "title": "Problema no sistema",
        "status": "aberto",
        "priority": "alta",
        "user_name": "João Silva",
        "cliente_name": "Maria Santos",
        "created_at": "2025-11-20T10:30:00.000000Z"
      }
    ],
    "recent_messages": [
      {
        "id": 456,
        "ticket_id": 123,
        "ticket_title": "Problema no sistema",
        "user_name": "João Silva",
        "is_internal": false,
        "created_at": "2025-11-20T11:00:00.000000Z"
      }
    ]
  }
}
```

---

## 2. Estatísticas de Tickets

Retorna estatísticas detalhadas sobre os tickets do sistema.

### Endpoint
```
GET /api/admin/statistics/tickets
```

### Parâmetros de Query (Opcionais)
- `period` (string): Período de análise
  - Valores: `day`, `week`, `month`, `year`, `all`
  - Padrão: `month`

### Exemplo de Requisição
```bash
GET /api/admin/statistics/tickets?period=week
Authorization: Bearer {token}
```

### Resposta de Sucesso (200)
```json
{
  "period": "week",
  "start_date": "2025-11-17 00:00:00",
  "overview": {
    "total": 45,
    "abertos": 8,
    "pendentes": 10,
    "resolvidos": 20,
    "finalizados": 7,
    "alta_prioridade": 5,
    "media_prioridade": 30,
    "baixa_prioridade": 10
  },
  "by_status": {
    "aberto": 8,
    "pendente": 10,
    "resolvido": 20,
    "finalizado": 7
  },
  "by_priority": {
    "alta": 5,
    "média": 30,
    "baixa": 10
  },
  "by_day": [
    {
      "date": "2025-11-17",
      "total": 5
    },
    {
      "date": "2025-11-18",
      "total": 8
    }
  ],
  "by_user": [
    {
      "user_id": 1,
      "user_name": "João Silva",
      "total": 15
    },
    {
      "user_id": 2,
      "user_name": "Maria Santos",
      "total": 12
    }
  ],
  "by_cliente": [
    {
      "cliente_id": 10,
      "cliente_name": "Empresa ABC",
      "total": 8
    }
  ],
  "resolution_time": {
    "average_hours": 24.5,
    "average_days": 1.02,
    "min_hours": 2,
    "max_hours": 72
  }
}
```

---

## 3. Estatísticas de Usuários

Retorna estatísticas sobre usuários e performance da equipe.

### Endpoint
```
GET /api/admin/statistics/users
```

### Parâmetros de Query (Opcionais)
- `period` (string): Período de análise
  - Valores: `day`, `week`, `month`, `year`, `all`
  - Padrão: `month`

### Exemplo de Requisição
```bash
GET /api/admin/statistics/users?period=month
Authorization: Bearer {token}
```

### Resposta de Sucesso (200)
```json
{
  "period": "month",
  "start_date": "2025-11-01 00:00:00",
  "overview": {
    "total": 50,
    "admins": 3,
    "support": 10,
    "assistant": 15,
    "cliente": 22
  },
  "by_role": {
    "admin": 3,
    "support": 10,
    "assistant": 15,
    "cliente": 22
  },
  "top_performers": [
    {
      "user_id": 1,
      "user_name": "João Silva",
      "role": "support",
      "resolved_tickets": 45
    },
    {
      "user_id": 2,
      "user_name": "Maria Santos",
      "role": "support",
      "resolved_tickets": 38
    }
  ],
  "user_activity": {
    "active_users": 35,
    "total_users": 50,
    "activity_rate": 70.0
  }
}
```

---

## 4. Estatísticas de Mensagens

Retorna estatísticas sobre mensagens enviadas no sistema.

### Endpoint
```
GET /api/admin/statistics/messages
```

### Parâmetros de Query (Opcionais)
- `period` (string): Período de análise
  - Valores: `day`, `week`, `month`, `year`, `all`
  - Padrão: `month`

### Exemplo de Requisição
```bash
GET /api/admin/statistics/messages?period=week
Authorization: Bearer {token}
```

### Resposta de Sucesso (200)
```json
{
  "period": "week",
  "start_date": "2025-11-17 00:00:00",
  "overview": {
    "total": 320,
    "internal": 150,
    "external": 170
  },
  "by_day": [
    {
      "date": "2025-11-17",
      "total": 45
    },
    {
      "date": "2025-11-18",
      "total": 52
    }
  ],
  "by_user": [
    {
      "user_id": 1,
      "user_name": "João Silva",
      "total": 120
    },
    {
      "user_id": 2,
      "user_name": "Maria Santos",
      "total": 95
    }
  ],
  "internal_vs_external": {
    "internal": 150,
    "external": 170
  }
}
```

---

## 5. Estatísticas de Anexos

Retorna estatísticas sobre anexos enviados no sistema.

### Endpoint
```
GET /api/admin/statistics/attachments
```

### Parâmetros de Query (Opcionais)
- `period` (string): Período de análise
  - Valores: `day`, `week`, `month`, `year`, `all`
  - Padrão: `month`

### Exemplo de Requisição
```bash
GET /api/admin/statistics/attachments?period=month
Authorization: Bearer {token}
```

### Resposta de Sucesso (200)
```json
{
  "period": "month",
  "start_date": "2025-11-01 00:00:00",
  "overview": {
    "total": 85,
    "ticket_attachments": 50,
    "message_attachments": 35
  },
  "by_type": {
    "image/jpeg": 30,
    "image/png": 20,
    "application/pdf": 25,
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document": 10
  },
  "total_size": {
    "bytes": 52428800,
    "kb": 51200,
    "mb": 50,
    "gb": 0.05
  }
}
```

---

## 6. Tendências

Retorna dados de tendências ao longo do tempo para análise de crescimento.

### Endpoint
```
GET /api/admin/statistics/trends
```

### Parâmetros de Query (Opcionais)
- `days` (integer): Número de dias para análise
  - Padrão: `30`

### Exemplo de Requisição
```bash
GET /api/admin/statistics/trends?days=60
Authorization: Bearer {token}
```

### Resposta de Sucesso (200)
```json
{
  "days": 60,
  "start_date": "2025-09-21 00:00:00",
  "tickets_trend": [
    {
      "date": "2025-09-21",
      "total": 5
    },
    {
      "date": "2025-09-22",
      "total": 8
    }
  ],
  "messages_trend": [
    {
      "date": "2025-09-21",
      "total": 15
    },
    {
      "date": "2025-09-22",
      "total": 20
    }
  ],
  "users_trend": [
    {
      "date": "2025-09-21",
      "total": 2
    }
  ],
  "resolution_rate_trend": [
    {
      "date": "2025-09-21",
      "total": 10,
      "resolved": 6,
      "rate": 60.0
    },
    {
      "date": "2025-09-22",
      "total": 12,
      "resolved": 8,
      "rate": 66.67
    }
  ]
}
```

---

## 🔒 Segurança e Permissões

### Autenticação
Todas as rotas requerem:
- Token de autenticação Sanctum válido
- Header: `Authorization: Bearer {token}`

### Permissões
- **Apenas administradores** (`role: admin`) podem acessar estas rotas
- Usuários sem permissão receberão: `403 Forbidden`

### Exemplo de Resposta de Erro (403)
```json
{
  "message": "Acesso negado. Apenas administradores podem acessar."
}
```

---

## 📊 Casos de Uso

### Dashboard de Administração
Use `/dashboard` para criar um painel principal com visão geral do sistema.

### Análise de Performance
Use `/tickets` e `/users` para analisar performance da equipe e identificar top performers.

### Análise de Comunicação
Use `/messages` para entender padrões de comunicação e volume de mensagens.

### Gestão de Armazenamento
Use `/attachments` para monitorar uso de armazenamento e tipos de arquivos mais comuns.

### Análise de Tendências
Use `/trends` para identificar crescimento, padrões sazonais e tendências de resolução.

---

## 💡 Dicas de Implementação Frontend

### 1. Cache de Dados
- Considere implementar cache no frontend para reduzir requisições
- Dados de estatísticas podem ser atualizados a cada 5-10 minutos

### 2. Visualizações Recomendadas
- **Dashboard**: Cards com métricas principais + gráficos de linha/barra
- **Tickets**: Gráficos de pizza (status, prioridade) + gráficos de linha (tendência)
- **Usuários**: Tabela de top performers + gráfico de barras por role
- **Mensagens**: Gráfico de linha temporal + comparação interno vs externo
- **Anexos**: Gráfico de pizza por tipo + indicador de tamanho total
- **Tendências**: Múltiplos gráficos de linha para comparação

### 3. Filtros de Período
- Implemente um seletor de período (dia, semana, mês, ano, todos)
- Atualize automaticamente os gráficos ao mudar o período

### 4. Exportação de Dados
- Considere adicionar funcionalidade de exportação (CSV/PDF) no frontend
- Use os dados JSON retornados para gerar relatórios

---

## 🚀 Exemplo de Integração Frontend

```javascript
// Exemplo usando fetch
async function getDashboardStats(period = 'month') {
  const response = await fetch(
    `/api/admin/statistics/dashboard?period=${period}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    }
  );
  
  if (!response.ok) {
    throw new Error('Erro ao buscar estatísticas');
  }
  
  return await response.json();
}

// Uso
const stats = await getDashboardStats('week');
console.log('Total de tickets:', stats.tickets.total);
console.log('Taxa de resolução:', stats.performance.resolution_rate);
```

---

## 📝 Notas Importantes

1. **Performance**: Queries podem ser pesadas em grandes volumes de dados. Considere implementar cache no backend se necessário.

2. **Períodos**: O parâmetro `period` afeta todas as consultas dentro de cada endpoint.

3. **Datas**: Todas as datas retornadas estão no formato ISO 8601.

4. **Limites**: Algumas listas (como top performers) são limitadas a 10 itens.

5. **Cálculos**: Taxas e médias são calculadas em tempo real. Valores podem variar ligeiramente entre requisições.

---

## 🔄 Changelog

- **2025-11-20**: Criação inicial da API de estatísticas
  - Dashboard geral
  - Estatísticas de tickets
  - Estatísticas de usuários
  - Estatísticas de mensagens
  - Estatísticas de anexos
  - Análise de tendências

