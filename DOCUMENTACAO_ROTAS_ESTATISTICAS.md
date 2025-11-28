# 📚 Documentação das Rotas de Estatísticas

Documentação completa das rotas de estatísticas: o que retornam e como usar.

---

## 🔐 Autenticação

Todas as rotas requerem autenticação via **Bearer Token** no header:

```
Authorization: Bearer {seu_token}
```

---

## 📅 Parâmetros de Período

Todas as rotas suportam o parâmetro `period` via query string:

- `day` - Hoje
- `week` - Esta semana  
- `month` - Este mês (padrão)
- `year` - Este ano
- `all` - Todos os dados

**Exemplo:** `GET /api/statistics/my-stats?period=week`

---

## 👤 ROTAS PESSOAIS (Qualquer Usuário Autenticado)

### 1. Estatísticas e Métricas

**Rota:** `GET /api/statistics/my-stats?period=month`

**Descrição:** Retorna estatísticas completas dos tickets atribuídos ao usuário logado.

#### Estrutura da Resposta:

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

#### Campos Principais:

**`overview`** - Visão geral dos tickets:
- `total` - Total de tickets
- `abertos`, `pendentes`, `resolvidos`, `finalizados` - Por status
- `alta_prioridade`, `media_prioridade`, `baixa_prioridade` - Por prioridade

**`productivity`** - Métricas de produtividade:
- `tickets_assigned` - Tickets atribuídos ao usuário
- `tickets_closed` - Tickets fechados
- `tickets_responded` - Tickets respondidos
- `resolution_rate` - Taxa de resolução (%)
- `response_rate` - Taxa de resposta (%)
- `average_response_time_hours` - Tempo médio de resposta (horas)
- `average_resolution_time_hours` - Tempo médio de resolução (horas)

**`response_time`** - Tempos de resposta:
- `first_response` - Tempo até primeira resposta
- `resolution_time` - Tempo até resolução
- `total_open_time` - Tempo total aberto

**`by_day`** - Tickets criados por dia (para gráficos de linha)

**`tickets_by_origin`** - Distribuição por origem (formulário, email, API, etc.)

**`tickets_created_by_period`** - Tickets criados agrupados por período

**`tickets_closed_by_period`** - Tickets fechados agrupados por período (com comparação criados vs fechados)

---

### 2. Histórico de Atividades

**Rota:** `GET /api/statistics/my-activity?period=month&limit=50`

**Descrição:** Retorna histórico completo de todas as atividades do usuário logado.

**Parâmetros:**
- `period` (opcional): `day`, `week`, `month`, `year`, `all` (padrão: `month`)
- `limit` (opcional): Número máximo de atividades na timeline (padrão: 50)

#### Estrutura da Resposta:

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
  "summary": {
    "tickets_created": 15,
    "tickets_updated": 12,
    "messages_sent": 45,
    "attachments_uploaded": 8
  },
  "timeline": [
    {
      "type": "ticket_created",
      "id": 123,
      "title": "Problema no sistema",
      "description": "Ticket criado: Problema no sistema",
      "status": "aberto",
      "priority": "alta",
      "created_at": "2025-11-15T10:30:00.000000Z"
    },
    {
      "type": "ticket_updated",
      "id": 123,
      "title": "Problema no sistema",
      "description": "Ticket atualizado: Problema no sistema",
      "status": "resolvido",
      "priority": "alta",
      "created_at": "2025-11-15T14:20:00.000000Z"
    },
    {
      "type": "message_sent",
      "id": 456,
      "ticket_id": 123,
      "ticket_title": "Problema no sistema",
      "description": "Mensagem enviada",
      "message_preview": "Olá, vou analisar o problema...",
      "is_internal": false,
      "created_at": "2025-11-15T10:35:00.000000Z"
    },
    {
      "type": "attachment_uploaded",
      "id": 789,
      "ticket_id": 123,
      "ticket_title": "Problema no sistema",
      "description": "Anexo enviado: screenshot.png",
      "file_name": "screenshot.png",
      "file_size": 245760,
      "created_at": "2025-11-15T10:40:00.000000Z"
    }
  ],
  "tickets_created": [
    {
      "id": 123,
      "title": "Problema no sistema",
      "status": "aberto",
      "priority": "alta",
      "created_at": "2025-11-15T10:30:00.000000Z",
      "updated_at": "2025-11-15T10:30:00.000000Z"
    }
  ],
  "tickets_updated": [
    {
      "id": 123,
      "title": "Problema no sistema",
      "status": "resolvido",
      "priority": "alta",
      "created_at": "2025-11-15T10:30:00.000000Z",
      "updated_at": "2025-11-15T14:20:00.000000Z"
    }
  ],
  "messages_sent": [
    {
      "id": 456,
      "ticket_id": 123,
      "ticket_title": "Problema no sistema",
      "message": "Olá, vou analisar o problema...",
      "is_internal": false,
      "created_at": "2025-11-15T10:35:00.000000Z"
    }
  ],
  "attachments_uploaded": [
    {
      "id": 789,
      "type": "ticket_attachment",
      "ticket_id": 123,
      "ticket_title": "Problema no sistema",
      "file_name": "screenshot.png",
      "file_type": "image/png",
      "file_size": 245760,
      "created_at": "2025-11-15T10:40:00.000000Z"
    }
  ]
}
```

#### Campos Principais:

**`summary`** - Resumo numérico:
- `tickets_created` - Quantidade de tickets criados
- `tickets_updated` - Quantidade de tickets atualizados
- `messages_sent` - Quantidade de mensagens enviadas
- `attachments_uploaded` - Quantidade de anexos enviados

**`timeline`** - Lista cronológica de todas as atividades (ordenada por data, mais recente primeiro):
- `type` - Tipo de atividade: `ticket_created`, `ticket_updated`, `message_sent`, `attachment_uploaded`
- `description` - Descrição da atividade
- `created_at` - Data/hora da atividade
- Campos específicos dependem do tipo

**`tickets_created`** - Lista completa de tickets criados pelo usuário

**`tickets_updated`** - Lista completa de tickets atualizados pelo usuário

**`messages_sent`** - Lista completa de mensagens enviadas pelo usuário

**`attachments_uploaded`** - Lista completa de anexos enviados pelo usuário

#### Tipos de Atividade na Timeline:

1. **`ticket_created`** - Quando um ticket foi criado
   - Campos: `id`, `title`, `status`, `priority`, `description`, `created_at`

2. **`ticket_updated`** - Quando um ticket foi atualizado
   - Campos: `id`, `title`, `status`, `priority`, `description`, `created_at`

3. **`message_sent`** - Quando uma mensagem foi enviada
   - Campos: `id`, `ticket_id`, `ticket_title`, `description`, `message_preview`, `is_internal`, `created_at`

4. **`attachment_uploaded`** - Quando um anexo foi enviado
   - Campos: `id`, `ticket_id`, `ticket_title`, `description`, `file_name`, `file_size`, `created_at`

---

## 🔒 ROTAS ADMINISTRATIVAS (Apenas Admin)

### 3. Estatísticas Pessoais do Admin

**Rota:** `GET /api/admin/statistics/my-stats?period=month`

**Descrição:** Retorna as mesmas estatísticas da rota `/api/statistics/my-stats`, mas dentro do grupo de rotas administrativas.

**Estrutura da Resposta:** Idêntica à rota `/api/statistics/my-stats`

**Uso:** Use esta rota quando estiver em uma área administrativa para manter consistência com outras rotas admin.

---

### 4. Comparar Performance

**Rota:** `GET /api/admin/statistics/compare-performance?period=month`

**Descrição:** Compara a performance do administrador logado com a média de todos os outros usuários do sistema.

#### Estrutura da Resposta:

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
      "tickets_not_resolved": 5,
      "tickets_responded": 22,
      "resolution_rate": 80.0,
      "response_rate": 90.0,
      "average_response_time_minutes": 30.0,
      "average_response_time_hours": 0.5,
      "average_resolution_time_minutes": 120.0,
      "average_resolution_time_hours": 2.0
    },
    "response_time": {
      "first_response": {
        "average_minutes": 30.0,
        "average_hours": 0.5
      },
      "resolution_time": {
        "average_minutes": 120.0,
        "average_hours": 2.0
      }
    },
    "overview": {
      "total": 25,
      "resolvidos": 20,
      "finalizados": 0
    }
  },
  "average_others": {
    "productivity": {
      "tickets_assigned": 18.5,
      "tickets_closed": 15.2,
      "tickets_not_resolved": 3.3,
      "tickets_responded": 16.8,
      "resolution_rate": 75.5,
      "response_rate": 85.0,
      "average_response_time_minutes": 48.0,
      "average_response_time_hours": 0.8,
      "average_resolution_time_minutes": 150.0,
      "average_resolution_time_hours": 2.5
    },
    "response_time": {
      "first_response": {
        "average_minutes": 48.0,
        "average_hours": 0.8
      },
      "resolution_time": {
        "average_minutes": 150.0,
        "average_hours": 2.5
      }
    },
    "overview": {
      "total": 18.5,
      "resolvidos": 15.2,
      "finalizados": 0
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

#### Campos Principais:

**`my_performance`** - Sua performance pessoal:
- Mesma estrutura de `productivity`, `response_time` e `overview` da rota `my-stats`

**`average_others`** - Média dos outros usuários:
- Mesma estrutura, mas com valores médios
- `total_users` - Quantidade de usuários usados para calcular a média

**`comparison`** - Comparação detalhada para cada métrica:

Para cada métrica (ex: `tickets_assigned`, `resolution_rate`, etc.):
- `my_value` - Seu valor pessoal
- `average_value` - Média dos outros usuários
- `difference_percent` - Diferença percentual:
  - Positivo = você está acima da média
  - Negativo = você está abaixo da média
  - Para tempos: negativo é melhor (menor tempo = melhor)
- `status` - Status da comparação:
  - `"better"` - Você está significativamente melhor (>10% de diferença)
  - `"worse"` - Você está significativamente pior (>10% de diferença)
  - `"similar"` - Você está similar à média (±10%)

#### Métricas Comparadas:

1. **`tickets_assigned`** - Tickets atribuídos (maior = melhor)
2. **`tickets_closed`** - Tickets fechados (maior = melhor)
3. **`resolution_rate`** - Taxa de resolução % (maior = melhor)
4. **`response_rate`** - Taxa de resposta % (maior = melhor)
5. **`average_response_time`** - Tempo médio de resposta em horas (menor = melhor)
6. **`average_resolution_time`** - Tempo médio de resolução em horas (menor = melhor)
7. **`first_response_time`** - Tempo de primeira resposta em horas (menor = melhor)

**Nota Importante:** Para métricas de tempo (response_time, resolution_time), valores negativos em `difference_percent` são melhores, pois significam menor tempo.

---

## 📊 Como Usar os Dados

### Para Gráficos e Visualizações:

**Gráfico de Barras - Produtividade:**
- Use `productivity.tickets_assigned`, `tickets_closed`, `tickets_responded`
- Labels: "Tickets Atribuídos", "Tickets Fechados", "Tickets Respondidos"

**Gráfico de Pizza - Status:**
- Use `by_status` (aberto, pendente, resolvido, finalizado)
- Labels: "Aberto", "Pendente", "Resolvido", "Finalizado"

**Gráfico de Pizza - Prioridade:**
- Use `by_priority` (alta, média, baixa)
- Labels: "Alta", "Média", "Baixa"

**Gráfico de Pizza - Origens:**
- Use `tickets_by_origin.by_origin`
- Labels: "Formulário Web", "E-mail", "API", "Telefone/Manual"

**Gráfico de Linha - Tickets por Dia:**
- Use `by_day` ou `tickets_created_by_period`
- Eixo X: `date` ou `period`
- Eixo Y: `total`

**Gráfico de Barras Agrupadas - Criados vs Fechados:**
- Use `tickets_closed_by_period`
- Eixo X: `period`
- Séries: `created` (criados) e `closed` (fechados)

**Timeline de Atividades:**
- Use `timeline` da rota `my-activity`
- Ordenar por `created_at` (já vem ordenado)
- Agrupar por tipo (`type`) ou por data

**Comparação de Performance:**
- Use `comparison` da rota `compare-performance`
- Para cada métrica, mostrar `my_value` vs `average_value`
- Usar `status` para colorir (verde=better, vermelho=worse, cinza=similar)
- Mostrar `difference_percent` como indicador

---

## 🎯 Casos de Uso

### Dashboard Pessoal:
- Use `/api/statistics/my-stats` para mostrar:
  - Cards com `overview.total`, `overview.resolvidos`
  - Taxa de resolução (`productivity.resolution_rate`)
  - Tempo médio de resposta (`response_time.first_response.average_hours`)
  - Gráfico de tickets por dia (`by_day`)

### Histórico de Ações:
- Use `/api/statistics/my-activity` para mostrar:
  - Resumo (`summary`)
  - Timeline cronológica (`timeline`)
  - Lista de tickets criados (`tickets_created`)
  - Lista de mensagens (`messages_sent`)

### Comparação de Performance (Admin):
- Use `/api/admin/statistics/compare-performance` para mostrar:
  - Cards comparando cada métrica
  - Indicadores visuais baseados em `status`
  - Percentual de diferença (`difference_percent`)

---

## ⚠️ Observações Importantes

1. **Autenticação:** Sempre inclua o token no header `Authorization: Bearer {token}`

2. **Períodos:** Use `day`, `week`, `month`, `year` ou `all` no parâmetro `period`

3. **Permissões:** 
   - Rotas `/api/statistics/*` - Qualquer usuário autenticado
   - Rotas `/api/admin/statistics/*` - Apenas admin

4. **Formato de Datas:** Todas as datas retornadas estão no formato ISO 8601

5. **Percentuais:** Todos os percentuais são números (ex: 85.71 = 85.71%)

6. **Tempos:** 
   - Estão disponíveis em minutos e horas
   - Use `average_hours` para exibição mais legível

7. **Status de Comparação:**
   - `"better"` = >10% de diferença (melhor)
   - `"worse"` = >10% de diferença (pior)
   - `"similar"` = ±10% de diferença (similar)

8. **Tempos na Comparação:**
   - Para métricas de tempo, valores negativos em `difference_percent` são melhores
   - Exemplo: `-37.5%` significa que você é 37.5% mais rápido que a média

---

## 📋 Resumo das Rotas

| Rota | Usuário | Retorna | Uso Principal |
|------|---------|---------|---------------|
| `/api/statistics/my-stats` | Qualquer | Estatísticas e métricas | Dashboard pessoal, gráficos |
| `/api/statistics/my-activity` | Qualquer | Histórico de atividades | Timeline, lista de ações |
| `/api/admin/statistics/my-stats` | Admin | Estatísticas (mesmo que acima) | Dashboard admin pessoal |
| `/api/admin/statistics/compare-performance` | Admin | Comparação com média | Análise de performance |

---

**Última atualização:** Novembro 2025

