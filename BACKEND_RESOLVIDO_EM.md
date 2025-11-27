# 📋 Campo `resolvido_em` - Documentação Backend

## 🎯 Visão Geral

O campo `resolvido_em` é um campo **opcional** do tipo `timestamp` que permite definir manualmente a data e horário em que um ticket foi resolvido. Este campo tem **prioridade máxima** no cálculo de tempo de resolução.

---

## 🗄️ Estrutura do Banco de Dados

### Migration
```php
// database/migrations/2025_11_24_014200_add_tempo_resolucao_to_tickets_table.php
$table->timestamp('resolvido_em')->nullable()->after('tempo_resolucao')
    ->comment('Data e horário em que o ticket foi resolvido. Opcional.');
```

### Tabela `tickets`
| Campo | Tipo | Nullable | Descrição |
|-------|------|----------|-----------|
| `resolvido_em` | `timestamp` | ✅ Sim | Data/hora de resolução (opcional) |

---

## 📤 Formato de Entrada (API)

### Formato Aceito
```json
{
  "resolvido_em": "2025-11-20T14:30:00"
}
```

### Formatos Válidos
- ✅ `"2025-11-20T14:30:00"` (com segundos)
- ✅ `"2025-11-20T14:30"` (sem segundos)
- ✅ `"2025-11-20 14:30:00"` (espaço ao invés de T)
- ✅ `null` (para remover o valor)
- ❌ `"2025-11-20"` (sem hora - inválido)

### Exemplos de Requisição

#### Criar Ticket com resolvido_em
```http
POST /api/tickets
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Problema no sistema",
  "nome_cliente": "Cliente ABC",
  "descricao": "Sistema está lento",
  "status": "resolvido",
  "priority": "alta",
  "resolvido_em": "2025-11-20T15:30:00"
}
```

#### Atualizar Ticket - Definir resolvido_em
```http
PUT /api/tickets/123
Content-Type: application/json
Authorization: Bearer {token}

{
  "status": "resolvido",
  "resolvido_em": "2025-11-20T18:45:00"
}
```

#### Atualizar Ticket - Remover resolvido_em
```http
PUT /api/tickets/123
Content-Type: application/json
Authorization: Bearer {token}

{
  "resolvido_em": null
}
```

---

## ✅ Validações Implementadas

### Validação no Controller
```php
// app/Http/Controllers/TicketController.php
$validationRules = [
    'resolvido_em' => 'nullable|date',
];
```

### Regras de Validação
1. ✅ **Tipo**: Deve ser uma data válida (`date`)
2. ✅ **Opcional**: Pode ser `null` ou não enviado
3. ✅ **Formato**: Aceita formatos ISO 8601 e variações

### Validações Lógicas Recomendadas (Frontend)
- ⚠️ `resolvido_em` não deve ser anterior a `created_at`
- ⚠️ `resolvido_em` não deve ser no futuro (a menos que seja permitido)

> **Nota**: A validação de "não ser anterior a `created_at`" deve ser implementada no frontend ou adicionada como validação customizada no backend se necessário.

---

## 📥 Formato de Resposta (API)

### Resposta Padrão
```json
{
  "id": 123,
  "title": "Problema no sistema",
  "status": "resolvido",
  "priority": "alta",
  "resolvido_em": "2025-11-20T14:30:00.000000Z",
  "created_at": "2025-11-20T10:00:00.000000Z",
  "updated_at": "2025-11-20T14:30:00.000000Z",
  "user": { ... },
  "cliente": { ... }
}
```

### Quando `resolvido_em` é `null`
```json
{
  "id": 123,
  "resolvido_em": null,
  "created_at": "2025-11-20T10:00:00.000000Z",
  "updated_at": "2025-11-20T12:00:00.000000Z"
}
```

---

## 🧮 Cálculo de Tempo de Resolução

### Prioridade de Cálculo

O sistema usa a seguinte ordem de prioridade para calcular o tempo de resolução:

1. **`resolvido_em`** (maior prioridade)
   - Se preenchido: `tempo = resolvido_em - created_at`
   - Exemplo: Se criado em `10:00` e resolvido em `14:30` → `4h 30min`

2. **`tempo_resolucao`** (segunda prioridade)
   - Se preenchido e `resolvido_em` não: usa o valor em minutos
   - Exemplo: `tempo_resolucao = 120` → `2 horas`

3. **Cálculo Automático** (terceira prioridade)
   - Se nenhum estiver preenchido: `tempo = updated_at - created_at`
   - Exemplo: Se criado em `10:00` e atualizado em `12:00` → `2 horas`

### Implementação no StatisticsController

```php
// app/Http/Controllers/StatisticsController.php

private function getResolutionTimeStats($startDate)
{
    $resolvedTickets = Ticket::where('created_at', '>=', $startDate)
        ->whereIn('status', ['resolvido', 'finalizado'])
        ->get();

    $times = $resolvedTickets->map(function ($ticket) {
        // 1. Prioridade: resolvido_em
        if ($ticket->resolvido_em !== null) {
            return $ticket->resolvido_em->diffInHours($ticket->created_at);
        }
        // 2. Tempo manual em minutos
        if ($ticket->tempo_resolucao !== null) {
            return $ticket->tempo_resolucao / 60;
        }
        // 3. Calcular automaticamente
        return $ticket->updated_at->diffInHours($ticket->created_at);
    });

    return [
        'average_hours' => round($times->avg(), 2),
        'resolvido_em_count' => $resolvedTickets->filter(function ($ticket) {
            return $ticket->resolvido_em !== null;
        })->count(),
        // ...
    ];
}
```

---

## 🔧 Modelo Eloquent

### Model Ticket
```php
// app/Models/Ticket.php

protected $fillable = [
    // ... outros campos
    'resolvido_em',
];

protected $casts = [
    'resolvido_em' => 'datetime',
];
```

### Uso no Model
```php
$ticket = Ticket::find(123);

// Verificar se tem data de resolução
if ($ticket->resolvido_em) {
    $tempo = $ticket->resolvido_em->diffInMinutes($ticket->created_at);
}

// Definir data de resolução
$ticket->resolvido_em = '2025-11-20 14:30:00';
$ticket->save();

// Remover data de resolução
$ticket->resolvido_em = null;
$ticket->save();
```

---

## 📊 Estatísticas

### Resposta das Estatísticas
```json
{
  "resolution_time": {
    "average_hours": 2.5,
    "average_minutes": 150.0,
    "resolvido_em_count": 15,  // Quantos tickets têm resolvido_em
    "tempo_resolucao_count": 3, // Quantos tickets têm tempo_resolucao
    "calculated_time_count": 5  // Quantos tickets usam cálculo automático
  }
}
```

### Endpoint
```
GET /api/admin/statistics/tickets?period=month
```

---

## 🔄 Casos de Uso

### Caso 1: Resolver Ticket Agora
```php
$ticket = Ticket::find(123);
$ticket->status = 'resolvido';
$ticket->resolvido_em = now(); // Data/hora atual
$ticket->save();
```

### Caso 2: Resolver Ticket com Data Passada
```php
$ticket = Ticket::find(123);
$ticket->status = 'resolvido';
$ticket->resolvido_em = '2025-11-20 14:30:00'; // Data/hora específica
$ticket->save();
```

### Caso 3: Remover Data de Resolução
```php
$ticket = Ticket::find(123);
$ticket->resolvido_em = null; // Remove a data
$ticket->save();
```

### Caso 4: Buscar Tickets Resolvidos em um Período
```php
// Tickets resolvidos entre duas datas
$tickets = Ticket::whereBetween('resolvido_em', [
    '2025-11-01 00:00:00',
    '2025-11-30 23:59:59'
])->get();

// Tickets resolvidos hoje
$tickets = Ticket::whereDate('resolvido_em', today())->get();
```

---

## ⚠️ Considerações Importantes

### 1. Timezone
- O Laravel armazena timestamps em UTC
- A conversão para timezone local deve ser feita no frontend
- Ao enviar, use o formato ISO 8601 sem timezone (o Laravel interpreta como UTC)

### 2. Validação de Data
- O Laravel valida o formato da data automaticamente
- Recomenda-se validar no frontend que `resolvido_em >= created_at`
- Pode adicionar validação customizada no backend se necessário

### 3. Relação com Status
- `resolvido_em` pode ser definido mesmo se `status` não for "resolvido" ou "finalizado"
- Recomenda-se definir `resolvido_em` quando mudar status para "resolvido" ou "finalizado"

### 4. Performance
- O campo é indexado automaticamente pelo Laravel (timestamps)
- Queries com `whereDate('resolvido_em', ...)` são eficientes

---

## 🧪 Testes

### Exemplo de Teste
```php
// tests/Feature/TicketResolvidoEmTest.php

public function test_can_set_resolvido_em()
{
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
        'created_at' => '2025-11-20 10:00:00'
    ]);

    $response = $this->actingAs($user)
        ->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'resolvido',
            'resolvido_em' => '2025-11-20 14:30:00'
        ]);

    $response->assertStatus(200);
    $this->assertNotNull($ticket->fresh()->resolvido_em);
}

public function test_resolvido_em_calculates_time_correctly()
{
    $ticket = Ticket::create([
        'title' => 'Test',
        'created_at' => '2025-11-20 10:00:00',
        'resolvido_em' => '2025-11-20 14:30:00'
    ]);

    $tempo = $ticket->resolvido_em->diffInMinutes($ticket->created_at);
    $this->assertEquals(270, $tempo); // 4h 30min = 270 minutos
}
```

---

## 📝 Changelog

- **2025-11-24**: Campo `resolvido_em` adicionado
  - Migration criada
  - Model atualizado
  - Controller atualizado com validação
  - StatisticsController atualizado para usar `resolvido_em` com prioridade
  - Documentação criada

---

## 🔗 Referências

- **Migration**: `database/migrations/2025_11_24_014200_add_tempo_resolucao_to_tickets_table.php`
- **Model**: `app/Models/Ticket.php`
- **Controller**: `app/Http/Controllers/TicketController.php`
- **Statistics**: `app/Http/Controllers/StatisticsController.php`
- **Documentação Frontend**: `TEMPO_RESOLUCAO_FRONTEND.md`

---

## 💡 Dicas

1. **Sempre use `resolvido_em` ao invés de `tempo_resolucao`** quando possível, pois é mais preciso
2. **Defina `resolvido_em` quando mudar status para "resolvido" ou "finalizado"**
3. **Use `Carbon` para manipular datas** no backend Laravel
4. **Valide no frontend** que `resolvido_em >= created_at`
5. **Use `whereDate()` para queries por data** específica

---

**Última atualização**: 2025-11-24

