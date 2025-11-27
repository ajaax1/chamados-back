# ⏱️ Tempo de Resolução - Guia para Frontend

## 📋 Visão Geral

Foram adicionados dois novos campos opcionais nos tickets do sistema para registrar o tempo de resolução:

1. **`resolvido_em`** - Data e horário em que o ticket foi resolvido (recomendado)
2. **`tempo_resolucao`** - Tempo de resolução em minutos (alternativa)

### Características

#### Campo `resolvido_em`
- ✅ Campo **opcional** - pode ser enviado ou não
- ✅ Tipo: **datetime/timestamp** (data e horário)
- ✅ Formato: ISO 8601 (ex: `"2025-11-20T14:30:00"`)
- ✅ Quando preenchido, o sistema calcula automaticamente o tempo entre `created_at` e `resolvido_em`

#### Campo `tempo_resolucao`
- ✅ Campo **opcional** - pode ser enviado ou não
- ✅ Tipo: **integer** (número inteiro)
- ✅ Unidade: **minutos**
- ✅ Valor mínimo: **0** (não pode ser negativo)
- ✅ Usado quando `resolvido_em` não está preenchido

### Prioridade de Cálculo
1. **`resolvido_em`** (maior prioridade) - Se preenchido, calcula tempo entre `created_at` e `resolvido_em`
2. **`tempo_resolucao`** - Se preenchido e `resolvido_em` não, usa o valor em minutos
3. **Cálculo automático** - Se nenhum estiver preenchido, calcula pela diferença entre `created_at` e `updated_at`

---

## 🔄 Mudanças na API

### 1. Criar Ticket (POST /api/tickets)

#### Antes
```json
{
  "title": "Título do chamado",
  "nome_cliente": "Nome do Cliente",
  "descricao": "Descrição do problema",
  "status": "aberto",
  "priority": "média"
}
```

#### Agora (com campos opcionais de tempo)
```json
{
  "title": "Título do chamado",
  "nome_cliente": "Nome do Cliente",
  "descricao": "Descrição do problema",
  "status": "aberto",
  "priority": "média",
  "resolvido_em": "2025-11-20T14:30:00",  // Opcional: data/hora de resolução (recomendado)
  "tempo_resolucao": 120  // Opcional: tempo em minutos (alternativa)
}
```

#### Exemplo de Requisição
```javascript
const createTicket = async (ticketData) => {
  const response = await fetch('/api/tickets', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      title: ticketData.title,
      nome_cliente: ticketData.nomeCliente,
      descricao: ticketData.descricao,
      status: ticketData.status,
      priority: ticketData.priority,
      resolvido_em: ticketData.resolvidoEm || null, // Opcional: data/hora
      tempo_resolucao: ticketData.tempoResolucao || null // Opcional: minutos
    })
  });
  
  return await response.json();
};
```

---

### 2. Atualizar Ticket (PUT /api/tickets/{id})

#### Antes
```json
{
  "status": "resolvido",
  "priority": "alta"
}
```

#### Agora (pode incluir resolvido_em ou tempo_resolucao)
```json
{
  "status": "resolvido",
  "priority": "alta",
  "resolvido_em": "2025-11-20T16:45:00",  // Opcional: data/hora de resolução (recomendado)
  "tempo_resolucao": 90  // Opcional: tempo em minutos (alternativa)
}
```

#### Exemplo de Requisição
```javascript
const updateTicket = async (ticketId, updates) => {
  const response = await fetch(`/api/tickets/${ticketId}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      status: updates.status,
      resolvido_em: updates.resolvidoEm || null, // Opcional: data/hora
      tempo_resolucao: updates.tempoResolucao || null // Opcional: minutos
    })
  });
  
  return await response.json();
};
```

---

### 3. Visualizar Ticket (GET /api/ticket/{id})

#### Resposta Agora Inclui
```json
{
  "id": 123,
  "title": "Título do chamado",
  "status": "resolvido",
  "priority": "alta",
  "resolvido_em": "2025-11-20T14:30:00.000000Z",  // Novo campo (null se não foi preenchido)
  "tempo_resolucao": 120,  // Novo campo (null se não foi preenchido)
  "created_at": "2025-11-20T10:00:00.000000Z",
  "updated_at": "2025-11-20T12:00:00.000000Z",
  "user": { ... },
  "cliente": { ... }
}
```

---

## 📊 Mudanças nas Estatísticas

### 1. Estatísticas de Tickets (GET /api/admin/statistics/tickets)

#### Nova Resposta - Campo `resolution_time` Atualizado
```json
{
  "resolution_time": {
    "average_hours": 2.5,
    "average_days": 0.1,
    "average_minutes": 150.0,  // NOVO
    "min_hours": 0.5,
    "max_hours": 8.0,
    "using_manual_time": true,  // NOVO: indica se algum ticket usa tempo manual
    "manual_time_count": 15,     // NOVO: quantos tickets têm tempo manual
    "calculated_time_count": 5,  // NOVO: quantos tickets usam cálculo automático
    "resolvido_em_count": 12,    // NOVO: quantos tickets têm data/hora de resolução
    "tempo_resolucao_count": 3  // NOVO: quantos tickets têm tempo em minutos
  },
  "resolution_time_by_cliente": [  // NOVO: tempo médio por cliente
    {
      "cliente_id": 10,
      "cliente_name": "Empresa ABC",
      "total_tickets": 8,
      "average_minutes": 120.5,
      "average_hours": 2.01,
      "average_days": 0.08,
      "min_minutes": 30,
      "max_minutes": 240
    }
  ]
}
```

---

### 2. Estatísticas de Usuários (GET /api/admin/statistics/users)

#### Nova Resposta - Campo `average_resolution_time_by_cliente`
```json
{
  "average_resolution_time_by_cliente": {  // NOVO
    "overall_average_minutes": 135.5,
    "overall_average_hours": 2.26,
    "overall_average_days": 0.09,
    "total_resolved": 50,
    "min_minutes": 15,
    "max_minutes": 480
  }
}
```

---

## 🎨 Implementação no Frontend

### 1. Formulário de Criar/Editar Ticket

#### Adicionar Campos de Tempo de Resolução (Opcional)

```jsx
// React Example
import React, { useState } from 'react';

function TicketForm({ ticket, onSubmit }) {
  // Opção 1: Data/Hora de resolução (RECOMENDADO)
  const [resolvidoEm, setResolvidoEm] = useState(
    ticket?.resolvido_em 
      ? new Date(ticket.resolvido_em).toISOString().slice(0, 16) 
      : ''
  );
  
  // Opção 2: Tempo em minutos (ALTERNATIVA)
  const [tempoResolucao, setTempoResolucao] = useState(ticket?.tempo_resolucao || '');
  
  const [showTempoResolucao, setShowTempoResolucao] = useState(false);
  const [tipoTempo, setTipoTempo] = useState('data'); // 'data' ou 'minutos'

  const handleSubmit = (e) => {
    e.preventDefault();
    
    const formData = {
      title: e.target.title.value,
      descricao: e.target.descricao.value,
      status: e.target.status.value,
      priority: e.target.priority.value,
    };

    // Adicionar tempo de resolução conforme o tipo escolhido
    if (showTempoResolucao) {
      if (tipoTempo === 'data' && resolvidoEm) {
        formData.resolvido_em = resolvidoEm;
      } else if (tipoTempo === 'minutos' && tempoResolucao) {
        formData.tempo_resolucao = parseInt(tempoResolucao);
      }
    }

    onSubmit(formData);
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Outros campos... */}
      
      {/* Campos opcionais de tempo de resolução */}
      <div className="form-group">
        <label>
          <input
            type="checkbox"
            checked={showTempoResolucao}
            onChange={(e) => setShowTempoResolucao(e.target.checked)}
          />
          {' '}Definir tempo de resolução manualmente
        </label>
        
        {showTempoResolucao && (
          <div className="mt-2">
            {/* Seletor de tipo */}
            <div className="mb-3">
              <label>
                <input
                  type="radio"
                  value="data"
                  checked={tipoTempo === 'data'}
                  onChange={(e) => setTipoTempo(e.target.value)}
                />
                {' '}Data e Horário de Resolução (Recomendado)
              </label>
              <br />
              <label>
                <input
                  type="radio"
                  value="minutos"
                  checked={tipoTempo === 'minutos'}
                  onChange={(e) => setTipoTempo(e.target.value)}
                />
                {' '}Tempo em Minutos
              </label>
            </div>

            {/* Campo de data/hora */}
            {tipoTempo === 'data' && (
              <div>
                <label>
                  Data e Horário de Resolução:
                  <input
                    type="datetime-local"
                    value={resolvidoEm}
                    onChange={(e) => setResolvidoEm(e.target.value)}
                    className="form-control"
                  />
                </label>
                <small className="text-muted">
                  O sistema calculará automaticamente o tempo entre criação e resolução
                </small>
              </div>
            )}

            {/* Campo de minutos */}
            {tipoTempo === 'minutos' && (
              <div>
                <label>
                  Tempo de Resolução (minutos):
                  <input
                    type="number"
                    min="0"
                    value={tempoResolucao}
                    onChange={(e) => setTempoResolucao(e.target.value)}
                    placeholder="Ex: 120 (para 2 horas)"
                    className="form-control"
                  />
                </label>
                <small className="text-muted">
                  Tempo total em minutos
                </small>
              </div>
            )}
          </div>
        )}
      </div>

      <button type="submit">Salvar</button>
    </form>
  );
}
```

---

### 2. Exibir Tempo de Resolução no Ticket

```jsx
function TicketDetails({ ticket }) {
  const formatTempoResolucao = (minutos) => {
    if (!minutos) return 'Não informado';
    
    const horas = Math.floor(minutos / 60);
    const mins = minutos % 60;
    
    if (horas > 0) {
      return `${horas}h ${mins}min`;
    }
    return `${mins}min`;
  };

  const formatDateTime = (dateString) => {
    if (!dateString) return 'Não informado';
    const date = new Date(dateString);
    return date.toLocaleString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // Calcular tempo se tiver resolvido_em
  const calcularTempo = () => {
    if (ticket.resolvido_em && ticket.created_at) {
      const criado = new Date(ticket.created_at);
      const resolvido = new Date(ticket.resolvido_em);
      const minutos = Math.floor((resolvido - criado) / (1000 * 60));
      return minutos;
    }
    return null;
  };

  const tempoCalculado = calcularTempo();

  return (
    <div className="ticket-details">
      <h2>{ticket.title}</h2>
      
      {/* Exibir data/hora de resolução se disponível */}
      {ticket.resolvido_em && (
        <div className="info-item">
          <strong>Resolvido em:</strong>{' '}
          {formatDateTime(ticket.resolvido_em)}
          <span className="badge badge-success ml-2">Data/Hora Manual</span>
          {tempoCalculado && (
            <span className="ml-2">
              ({formatTempoResolucao(tempoCalculado)})
            </span>
          )}
        </div>
      )}

      {/* Exibir tempo em minutos se disponível (e não tiver resolvido_em) */}
      {!ticket.resolvido_em && ticket.tempo_resolucao !== null && (
        <div className="info-item">
          <strong>Tempo de Resolução:</strong>{' '}
          {formatTempoResolucao(ticket.tempo_resolucao)}
          <span className="badge badge-info ml-2">Tempo Manual</span>
        </div>
      )}

      {/* Se não tiver nenhum, mostrar cálculo automático */}
      {!ticket.resolvido_em && ticket.tempo_resolucao === null && ticket.status === 'resolvido' && (
        <div className="info-item">
          <strong>Tempo de Resolução:</strong>{' '}
          {formatTempoResolucao(tempoCalculado || 0)}
          <span className="badge badge-secondary ml-2">Calculado Automaticamente</span>
        </div>
      )}
      
      {/* Outros campos... */}
    </div>
  );
}
```

---

### 3. Exibir Estatísticas de Tempo por Cliente

```jsx
function StatisticsDashboard() {
  const [stats, setStats] = useState(null);

  useEffect(() => {
    fetch('/api/admin/statistics/tickets?period=month', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
      .then(res => res.json())
      .then(data => setStats(data));
  }, []);

  if (!stats) return <div>Carregando...</div>;

  return (
    <div>
      <h2>Estatísticas de Resolução</h2>
      
      {/* Tempo médio geral */}
      <div className="stat-card">
        <h3>Tempo Médio de Resolução</h3>
        <p>
          {stats.resolution_time.average_hours} horas
          {' '}({stats.resolution_time.average_minutes} minutos)
        </p>
        {stats.resolution_time.using_manual_time && (
          <small className="text-info">
            {stats.resolution_time.manual_time_count} tickets com tempo manual
          </small>
        )}
      </div>

      {/* Tempo médio por cliente */}
      <div className="stat-card">
        <h3>Tempo Médio por Cliente</h3>
        <table>
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Tickets</th>
              <th>Tempo Médio</th>
            </tr>
          </thead>
          <tbody>
            {stats.resolution_time_by_cliente.map((item, index) => (
              <tr key={index}>
                <td>{item.cliente_name}</td>
                <td>{item.total_tickets}</td>
                <td>
                  {item.average_hours.toFixed(2)}h
                  {' '}({item.average_minutes.toFixed(0)}min)
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
```

---

## 🔍 Validações

### Validações do Backend
- ✅ `resolvido_em` deve ser uma **data válida** (datetime/timestamp)
- ✅ `resolvido_em` é **opcional** (pode ser `null` ou não enviado)
- ✅ `tempo_resolucao` deve ser um **número inteiro** (integer)
- ✅ `tempo_resolucao` deve ser **>= 0** (não pode ser negativo)
- ✅ `tempo_resolucao` é **opcional** (pode ser `null` ou não enviado)

### Validações Recomendadas no Frontend
```javascript
// Validar data/hora de resolução
const validateResolvidoEm = (value, created_at) => {
  if (value === '' || value === null || value === undefined) {
    return { valid: true }; // Opcional, pode ser vazio
  }
  
  const resolvido = new Date(value);
  const criado = new Date(created_at);
  
  if (isNaN(resolvido.getTime())) {
    return { valid: false, error: 'Data inválida' };
  }
  
  if (resolvido < criado) {
    return { valid: false, error: 'Data de resolução não pode ser anterior à data de criação' };
  }
  
  return { valid: true };
};

// Validar tempo em minutos
const validateTempoResolucao = (value) => {
  if (value === '' || value === null || value === undefined) {
    return { valid: true }; // Opcional, pode ser vazio
  }
  
  const num = parseInt(value);
  
  if (isNaN(num)) {
    return { valid: false, error: 'Deve ser um número' };
  }
  
  if (num < 0) {
    return { valid: false, error: 'Não pode ser negativo' };
  }
  
  return { valid: true };
};
```

---

## 📝 Exemplos de Uso

### Exemplo 1: Criar Ticket SEM tempo de resolução
```javascript
const ticket = {
  title: "Problema no sistema",
  descricao: "Sistema está lento",
  status: "aberto",
  priority: "alta"
  // resolvido_em e tempo_resolucao não enviados - será calculado automaticamente
};
```

### Exemplo 2: Criar Ticket COM data/hora de resolução (RECOMENDADO)
```javascript
const ticket = {
  title: "Problema no sistema",
  descricao: "Sistema está lento",
  status: "resolvido",
  priority: "alta",
  resolvido_em: "2025-11-20T16:30:00"  // Data/hora de resolução
  // Sistema calcula automaticamente o tempo entre created_at e resolvido_em
};
```

### Exemplo 3: Criar Ticket COM tempo em minutos (ALTERNATIVA)
```javascript
const ticket = {
  title: "Problema no sistema",
  descricao: "Sistema está lento",
  status: "resolvido",
  priority: "alta",
  tempo_resolucao: 90  // 90 minutos = 1h30min
};
```

### Exemplo 4: Atualizar Ticket e definir data/hora de resolução
```javascript
// Quando resolver o ticket, definir a data/hora
const update = {
  status: "resolvido",
  resolvido_em: "2025-11-20T18:45:00"  // Data/hora de resolução
};
```

### Exemplo 5: Atualizar Ticket e definir tempo em minutos
```javascript
// Quando resolver o ticket, definir o tempo
const update = {
  status: "resolvido",
  tempo_resolucao: 120  // 2 horas
};
```

### Exemplo 4: Converter horas para minutos
```javascript
// Helper function
const horasParaMinutos = (horas) => {
  return Math.round(horas * 60);
};

// Uso
const tempoEmMinutos = horasParaMinutos(2.5); // 150 minutos
```

### Exemplo 5: Converter minutos para formato legível
```javascript
// Helper function
const formatarTempo = (minutos) => {
  if (!minutos) return 'Não informado';
  
  const dias = Math.floor(minutos / (60 * 24));
  const horas = Math.floor((minutos % (60 * 24)) / 60);
  const mins = minutos % 60;
  
  const parts = [];
  if (dias > 0) parts.push(`${dias}d`);
  if (horas > 0) parts.push(`${horas}h`);
  if (mins > 0) parts.push(`${mins}min`);
  
  return parts.join(' ') || '0min';
};

// Uso
formatarTempo(150); // "2h 30min"
formatarTempo(2880); // "2d"
formatarTempo(90); // "1h 30min"
```

### Exemplo 6: Converter data/hora para formato da API
```javascript
// Converter Date object para formato ISO 8601
const formatarDataParaAPI = (date) => {
  if (!date) return null;
  
  // Se for string, converter para Date
  const dataObj = typeof date === 'string' ? new Date(date) : date;
  
  // Retornar no formato ISO 8601 (sem timezone)
  const year = dataObj.getFullYear();
  const month = String(dataObj.getMonth() + 1).padStart(2, '0');
  const day = String(dataObj.getDate()).padStart(2, '0');
  const hours = String(dataObj.getHours()).padStart(2, '0');
  const minutes = String(dataObj.getMinutes()).padStart(2, '0');
  const seconds = String(dataObj.getSeconds()).padStart(2, '0');
  
  return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
};

// Converter datetime-local input para formato da API
const converterDatetimeLocalParaAPI = (datetimeLocal) => {
  if (!datetimeLocal) return null;
  // datetime-local retorna no formato: "2025-11-20T14:30"
  // API espera: "2025-11-20T14:30:00"
  return datetimeLocal + ':00';
};

// Uso
const agora = new Date();
formatarDataParaAPI(agora); // "2025-11-20T14:30:00"

// Para input datetime-local
const valorInput = "2025-11-20T14:30";
converterDatetimeLocalParaAPI(valorInput); // "2025-11-20T14:30:00"
```

---

## 🎯 Casos de Uso

### Caso 1: Ticket Resolvido Rapidamente
```javascript
// Cliente resolveu em 30 minutos
{
  status: "resolvido",
  tempo_resolucao: 30
}
```

### Caso 2: Ticket que Demorou Vários Dias
```javascript
// Ticket demorou 3 dias = 4320 minutos
{
  status: "resolvido",
  tempo_resolucao: 4320
}
```

### Caso 3: Deixar Sistema Calcular Automaticamente
```javascript
// Não enviar tempo_resolucao - sistema calcula pela diferença de datas
{
  status: "resolvido"
  // tempo_resolucao não enviado
}
```

---

## ⚠️ Importante

1. **Prioridade de Cálculo**:
   - **1ª prioridade**: `resolvido_em` - Se preenchido, calcula tempo entre `created_at` e `resolvido_em`
   - **2ª prioridade**: `tempo_resolucao` - Se preenchido e `resolvido_em` não, usa o valor em minutos
   - **3ª prioridade**: Cálculo automático - Se nenhum estiver preenchido, calcula pela diferença entre `created_at` e `updated_at`

2. **Recomendação**: Use `resolvido_em` (data/hora) ao invés de `tempo_resolucao` (minutos), pois é mais preciso e permite rastrear quando exatamente o ticket foi resolvido.

3. **Unidade**: 
   - `resolvido_em`: Formato ISO 8601 (datetime)
   - `tempo_resolucao`: **minutos** (número inteiro)

4. **Opcional**: Ambos os campos são completamente opcionais. Se nenhum for enviado, o sistema calculará automaticamente.

5. **Estatísticas**: As estatísticas agora mostram:
   - Tempo médio geral
   - Tempo médio por cliente
   - Quantidade de tickets com tempo manual vs calculado
   - Quantidade de tickets com `resolvido_em` vs `tempo_resolucao`

---

## 🚀 Checklist de Implementação

- [ ] Adicionar campo `resolvido_em` (datetime-local) no formulário de criar ticket (opcional)
- [ ] Adicionar campo `resolvido_em` no formulário de editar ticket (opcional)
- [ ] Adicionar campo `tempo_resolucao` (alternativa) no formulário (opcional)
- [ ] Exibir `resolvido_em` e `tempo_resolucao` na visualização do ticket
- [ ] Atualizar componente de estatísticas para mostrar tempo por cliente
- [ ] Adicionar validação no frontend para data/hora (não pode ser anterior à criação)
- [ ] Adicionar validação no frontend para minutos (número inteiro >= 0)
- [ ] Criar helper functions para converter horas/minutos
- [ ] Criar helper functions para formatar tempo legível
- [ ] Criar helper functions para formatar data/hora
- [ ] Atualizar tipos TypeScript/PropTypes se aplicável
- [ ] Testar criação de ticket com `resolvido_em`
- [ ] Testar criação de ticket com `tempo_resolucao`
- [ ] Testar criação de ticket sem tempo
- [ ] Testar atualização de ticket com `resolvido_em`
- [ ] Testar atualização de ticket com `tempo_resolucao`
- [ ] Verificar exibição nas estatísticas

---

## 📚 Referências

- **Migration**: `database/migrations/2025_11_24_014200_add_tempo_resolucao_to_tickets_table.php`
- **Model**: `app/Models/Ticket.php` (campo adicionado ao `$fillable`)
- **Controller**: `app/Http/Controllers/TicketController.php` (validação adicionada)
- **Statistics**: `app/Http/Controllers/StatisticsController.php` (novos métodos adicionados)

---

## 💡 Dicas

1. **Interface Amigável**: Considere permitir que o usuário insira em horas e converter para minutos automaticamente.

2. **Indicador Visual**: Mostre um badge ou ícone quando o tempo for manual vs calculado.

3. **Gráficos**: Use os novos dados de tempo por cliente para criar gráficos comparativos.

4. **Filtros**: Considere adicionar filtros nas estatísticas para ver apenas tickets com tempo manual ou apenas calculados.

---

**Última atualização**: 2025-11-24

