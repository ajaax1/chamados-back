# 📋 Resumo Completo das Features e Esquema do Banco de Dados

## 🎯 Features do Sistema

### 1. 🔐 Autenticação e Autorização
- **Login/Logout**: Sistema de autenticação via Laravel Sanctum
- **Reset de Senha**: Solicitação e redefinição de senha via email
- **Alteração de Senha**: Usuários autenticados podem alterar sua senha
- **Sistema de Roles**: 4 tipos de usuários com permissões específicas:
  - **Admin**: Acesso total ao sistema
  - **Support**: Pode gerenciar tickets e deletar
  - **Assistant**: Pode criar e responder tickets
  - **Cliente**: Pode criar e visualizar apenas seus próprios tickets

### 2. 🎫 Sistema de Tickets (Chamados)
- **Criação de Tickets**: Todos os usuários podem criar tickets
- **Campos do Ticket**:
  - Título (title)
  - Nome do cliente (nome_cliente)
  - Número WhatsApp (whatsapp_numero)
  - Descrição (descricao)
  - Status: `aberto`, `pendente`, `resolvido`, `finalizado`
  - Prioridade: `baixa`, `média`, `alta`
  - Atribuição a atendente (user_id)
  - Vinculação a cliente (cliente_id)
  - Tempo de resolução (tempo_resolucao em minutos)
  - Data de resolução (resolvido_em)
- **Filtros Avançados**:
  - Busca por termo (título, descrição, ID)
  - Filtro por status
  - Filtro por prioridade
  - Filtro por atendente (user_id)
  - Filtro por cliente (cliente_id)
  - Filtro por período (data inicial e final)
- **Estatísticas de Tickets**: Dashboard com métricas e gráficos

### 3. 💬 Sistema de Mensagens
- **Mensagens Internas**: Comunicação entre admin/support sobre tickets (não visíveis para clientes)
- **Mensagens Externas**: Comunicação visível para todos os envolvidos
- **Mensagens WhatsApp**: Integração com webhook do WhatsApp (legado)
- **Anexos em Mensagens**: Possibilidade de anexar arquivos às mensagens

### 4. 📎 Sistema de Anexos
- **Anexos de Tickets**: Upload de arquivos diretamente nos tickets
- **Anexos de Mensagens**: Upload de arquivos nas mensagens
- **Tipos Suportados**: JPEG, JPG, PNG, GIF, PDF, DOC, DOCX
- **Limites**: Máximo 10 arquivos por upload, 10MB por arquivo
- **Download e Visualização**: Endpoints para baixar e visualizar anexos
- **Gerenciamento**: Admin/Support/Assistant podem deletar anexos

### 5. 🔔 Sistema de Notificações
- **Notificações em Tempo Real**: Notificações quando tickets são atribuídos ou recebem mensagens
- **Gerenciamento**: Marcar como lida, marcar todas como lidas, deletar
- **Contadores**: Endpoint para contar notificações não lidas
- **Tipos de Notificação**:
  - Ticket atribuído (TicketAssignedNotification)
  - Nova mensagem no ticket (TicketMessageNotification)

### 6. 👥 Gerenciamento de Usuários
- **CRUD Completo**: Criar, listar, visualizar, atualizar e deletar usuários
- **Filtros**: Listagem alfabética, filtro por role
- **Estatísticas**: Dashboard com métricas de usuários
- **Perfil**: Usuários podem atualizar seu próprio perfil
- **Listagem de Clientes**: Endpoint específico para listar apenas clientes

### 7. 📊 Dashboard de Estatísticas (Admin)
- **Dashboard Geral**: Visão geral do sistema
- **Estatísticas de Tickets**:
  - Por status, prioridade, dia, usuário, cliente
  - Tempo médio de resolução
  - Tempo de resolução por cliente
- **Estatísticas de Usuários**:
  - Por role
  - Top performers
  - Atividade de usuários
  - Tempo médio de resolução por cliente
- **Estatísticas de Mensagens**:
  - Por dia, por usuário
  - Internas vs externas
- **Estatísticas de Anexos**:
  - Por tipo MIME
  - Tamanho total (bytes, KB, MB, GB)
- **Tendências**: Gráficos de crescimento ao longo do tempo
- **Filtros Temporais**: day, week, month, year, all

### 8. 🔗 Integração WhatsApp
- **Webhook**: Recebimento de mensagens do WhatsApp
- **Histórico**: Armazenamento de mensagens recebidas/enviadas

### 9. ⏱️ Tempo de Resolução
- **Cálculo Automático**: Baseado em created_at e updated_at
- **Tempo Manual**: Campo `tempo_resolucao` em minutos
- **Data de Resolução**: Campo `resolvido_em` para timestamp exato
- **Prioridade de Cálculo**:
  1. Se existe `resolvido_em`, usa diferença entre `resolvido_em` e `created_at`
  2. Se existe `tempo_resolucao`, usa esse valor
  3. Caso contrário, calcula automaticamente pela diferença de `updated_at` e `created_at`

---

## 🗄️ Esquema do Banco de Dados

### Tabela: `users`
Armazena informações dos usuários do sistema.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `name` | string | Nome do usuário |
| `email` | string (unique) | Email (único) |
| `email_verified_at` | timestamp (nullable) | Data de verificação do email |
| `password` | string | Senha (hasheada) |
| `role` | enum | Role: `admin`, `support`, `assistant`, `cliente` |
| `remember_token` | string (nullable) | Token de "lembrar-me" |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Relacionamentos:**
- `hasMany` tickets (como atendente - user_id)
- `hasMany` tickets (como cliente - cliente_id)
- `hasMany` ticket_messages

---

### Tabela: `tickets`
Armazena os chamados/tickets do sistema.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `title` | string(250) | Título do ticket |
| `nome_cliente` | string(100) | Nome do cliente |
| `whatsapp_numero` | string(20, nullable) | Número do WhatsApp |
| `user_id` | bigint (FK, nullable) | ID do atendente atribuído |
| `cliente_id` | bigint (FK, nullable) | ID do cliente (usuário) |
| `descricao` | text | Descrição do problema |
| `status` | enum | Status: `aberto`, `pendente`, `resolvido`, `finalizado` |
| `priority` | enum | Prioridade: `baixa`, `média`, `alta` |
| `tempo_resolucao` | integer (nullable) | Tempo de resolução em minutos |
| `resolvido_em` | timestamp (nullable) | Data/hora de resolução |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Relacionamentos:**
- `belongsTo` user (atendente)
- `belongsTo` cliente (User como cliente)
- `hasMany` whatsapp_messages
- `hasMany` ticket_messages
- `hasMany` ticket_attachments

**Índices:**
- `user_id` (FK para users)
- `cliente_id` (FK para users)

---

### Tabela: `ticket_messages`
Armazena mensagens internas e externas dos tickets.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `ticket_id` | bigint (FK) | ID do ticket |
| `user_id` | bigint (FK) | ID do usuário que enviou |
| `message` | text | Conteúdo da mensagem |
| `is_internal` | boolean | Se true, mensagem interna (só admin/support veem) |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Relacionamentos:**
- `belongsTo` ticket
- `belongsTo` user
- `hasMany` message_attachments

**Índices:**
- `ticket_id`
- `user_id`
- `created_at`

---

### Tabela: `message_attachments`
Armazena anexos das mensagens.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `ticket_message_id` | bigint (FK) | ID da mensagem |
| `nome_arquivo` | string | Nome original do arquivo |
| `caminho_arquivo` | string | Caminho relativo no storage |
| `tipo_mime` | string | Tipo MIME (ex: image/jpeg, application/pdf) |
| `tamanho` | unsigned bigint | Tamanho em bytes |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Relacionamentos:**
- `belongsTo` ticket_message

**Índices:**
- `ticket_message_id`

---

### Tabela: `ticket_attachments`
Armazena anexos dos tickets.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `ticket_id` | bigint (FK) | ID do ticket |
| `nome_arquivo` | string | Nome original do arquivo |
| `caminho_arquivo` | string | Caminho relativo no storage |
| `tipo_mime` | string | Tipo MIME (ex: image/jpeg, application/pdf) |
| `tamanho` | unsigned bigint | Tamanho em bytes |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Relacionamentos:**
- `belongsTo` ticket

---

### Tabela: `whatsapp_messages`
Armazena mensagens do WhatsApp (sistema legado).

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `ticket_id` | bigint (FK, nullable) | ID do ticket relacionado |
| `mensagem` | text | Conteúdo da mensagem |
| `tipo` | enum | Tipo: `recebido`, `enviado`, `sistema` |
| `criado_em` | timestamp | Data de criação |

**Relacionamentos:**
- `belongsTo` ticket

---

### Tabela: `notifications`
Armazena notificações do sistema (Laravel Notifications).

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid (PK) | Identificador único (UUID) |
| `type` | string | Tipo da notificação |
| `notifiable_type` | string | Tipo do modelo notificável |
| `notifiable_id` | bigint | ID do modelo notificável |
| `data` | text | Dados da notificação (JSON) |
| `read_at` | timestamp (nullable) | Data de leitura |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Relacionamentos:**
- Polimórfico com User (via notifiable_type/notifiable_id)

---

### Tabela: `password_reset_tokens`
Armazena tokens de reset de senha.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `email` | string (PK) | Email do usuário |
| `token` | string | Token de reset |
| `created_at` | timestamp (nullable) | Data de criação |

---

### Tabela: `personal_access_tokens`
Armazena tokens de autenticação do Laravel Sanctum.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `tokenable_type` | string | Tipo do modelo |
| `tokenable_id` | bigint | ID do modelo |
| `name` | string | Nome do token |
| `token` | string (unique) | Token (hasheado) |
| `abilities` | text (nullable) | Habilidades do token |
| `last_used_at` | timestamp (nullable) | Último uso |
| `expires_at` | timestamp (nullable) | Data de expiração |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Relacionamentos:**
- Polimórfico com User (via tokenable_type/tokenable_id)

---

### Tabela: `failed_jobs`
Armazena jobs que falharam na execução.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint (PK) | Identificador único |
| `uuid` | string (unique) | UUID único |
| `connection` | text | Conexão da fila |
| `queue` | text | Nome da fila |
| `payload` | longtext | Payload do job |
| `exception` | longtext | Exceção ocorrida |
| `failed_at` | timestamp | Data da falha |

---

## 🔗 Diagrama de Relacionamentos

```
users
  ├── tickets (user_id) → tickets
  ├── tickets (cliente_id) → tickets
  ├── ticket_messages → ticket_messages
  ├── notifications (polimórfico)
  └── personal_access_tokens (polimórfico)

tickets
  ├── user (user_id) → users
  ├── cliente (cliente_id) → users
  ├── whatsapp_messages → whatsapp_messages
  ├── ticket_messages → ticket_messages
  └── ticket_attachments → ticket_attachments

ticket_messages
  ├── ticket (ticket_id) → tickets
  ├── user (user_id) → users
  └── message_attachments → message_attachments

message_attachments
  └── ticket_message (ticket_message_id) → ticket_messages

ticket_attachments
  └── ticket (ticket_id) → tickets
```

---

## 📝 Observações Importantes

1. **Cascata de Deletação**:
   - Ao deletar um ticket, todas as mensagens, anexos e mensagens WhatsApp relacionadas são deletadas automaticamente
   - Ao deletar um usuário, os tickets atribuídos a ele ficam com `user_id = null` (nullOnDelete)
   - Ao deletar um cliente, os tickets ficam com `cliente_id = null` (nullOnDelete)

2. **Permissões por Role**:
   - **Admin**: Acesso total
   - **Support**: Pode gerenciar tickets e deletar
   - **Assistant**: Pode criar e responder tickets
   - **Cliente**: Pode criar e ver apenas seus próprios tickets

3. **Tempo de Resolução**:
   - Prioridade: `resolvido_em` > `tempo_resolucao` > cálculo automático
   - Apenas tickets com status `resolvido` ou `finalizado` são considerados

4. **Mensagens Internas**:
   - Mensagens com `is_internal = true` são visíveis apenas para admin e support
   - Clientes não veem mensagens internas

5. **Anexos**:
   - Máximo 10 arquivos por upload
   - Tamanho máximo: 10MB por arquivo
   - Tipos permitidos: jpeg, jpg, png, gif, pdf, doc, docx
   - Clientes não podem deletar anexos

---

## 🚀 Tecnologias Utilizadas

- **Framework**: Laravel (PHP)
- **Autenticação**: Laravel Sanctum
- **Notificações**: Laravel Notifications
- **Banco de Dados**: MySQL/PostgreSQL (via Eloquent ORM)
- **Storage**: Sistema de arquivos do Laravel


