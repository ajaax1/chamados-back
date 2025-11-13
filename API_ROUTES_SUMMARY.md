# 📋 Resumo Completo de Rotas da API

## 🔓 Rotas Públicas

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/login` | Autenticação |
| POST | `/api/password/forgot` | Solicitar reset de senha |
| GET | `/api/password/verify-token` | Verificar token de reset |
| POST | `/api/password/reset` | Resetar senha |
| POST | `/api/webhook/whatsapp` | Webhook do WhatsApp |

---

## 🔒 Rotas Protegidas (Requerem `Authorization: Bearer {token}`)

### 👤 Autenticação e Perfil

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/logout` | Logout |
| GET | `/api/me` | Perfil do usuário logado |
| PUT | `/api/me` | Atualizar perfil |
| POST | `/api/password/change` | Alterar senha (logado) |

### 👥 Usuários

| Método | Rota | Descrição | Permissão |
|--------|------|-----------|-----------|
| GET | `/api/users` | Listar usuários | Todos |
| GET | `/api/users-stats` | Estatísticas de usuários | Todos |
| POST | `/api/users` | Criar usuário | Admin |
| GET | `/api/users/{user}` | Ver usuário | Todos |
| PUT | `/api/users/{user}` | Atualizar usuário | Admin ou próprio |
| DELETE | `/api/users/{user}` | Deletar usuário | Admin |
| GET | `/api/users-alphabetical` | Listar usuários (ordem alfabética) | Todos |

**Novo:** Role `"cliente"` agora é válido ao criar usuários.

### 🎫 Tickets

| Método | Rota | Descrição | Permissão |
|--------|------|-----------|-----------|
| GET | `/api/tickets-filtro` | Listar tickets (com filtros) | Todos* |
| GET | `/api/tickets-stats` | Estatísticas de tickets | Todos* |
| GET | `/api/ticket/{id}` | Ver ticket | Todos* |
| POST | `/api/tickets` | Criar ticket | Todos |
| PUT | `/api/tickets/{ticket}` | Atualizar ticket | Admin/Support/Assistant |
| DELETE | `/api/tickets/{ticket}` | Deletar ticket | Support/Admin |

**Novo:** 
- Retorna `cliente` e `attachments` em todas as respostas
- Admin/Support podem definir `cliente_id` e `user_id`
- Clientes só veem seus próprios tickets automaticamente

**Filtros disponíveis:**
- `?search=termo` - Busca por título
- `?status=aberto` - Filtrar por status
- `?user_id=2` - Filtrar por atendente
- `?cliente_id=5` - **NOVO** - Filtrar por cliente
- `?priority=alta` - Filtrar por prioridade
- `?from=2025-01-01&to=2025-12-31` - Filtrar por data

### 💬 Mensagens

| Método | Rota | Descrição | Permissão |
|--------|------|-----------|-----------|
| GET | `/api/tickets/{ticket}/messages` | Listar mensagens | Todos* |
| POST | `/api/tickets/{ticket}/messages` | Enviar mensagem | Todos* |

*Baseado nas permissões do ticket

### 📎 Anexos (NOVO)

| Método | Rota | Descrição | Permissão |
|--------|------|-----------|-----------|
| POST | `/api/tickets/{ticket}/attachments` | Upload de arquivos | Todos* |
| GET | `/api/tickets/{ticket}/attachments` | Listar anexos | Todos* |
| GET | `/api/attachments/{attachment}` | Visualizar arquivo | Todos* |
| GET | `/api/attachments/{attachment}/download` | Download arquivo | Todos* |
| DELETE | `/api/attachments/{attachment}` | Deletar anexo | Admin/Support/Assistant |

**Especificações:**
- Máximo 10 arquivos por upload
- Tipos: jpeg, jpg, png, gif, pdf, doc, docx
- Tamanho máximo: 10MB por arquivo
- Clientes NÃO podem deletar anexos

---

## 📊 Resumo de Mudanças

### ✅ Adicionado
- Role `"cliente"` 
- Campo `cliente_id` nos tickets
- Sistema completo de anexos (5 novas rotas)
- Filtro `cliente_id` na listagem de tickets
- Relacionamento `cliente` nos tickets
- Relacionamento `attachments` nos tickets

### 🔄 Modificado
- `GET /api/tickets-filtro` - Agora retorna `cliente` e `attachments`
- `GET /api/ticket/{id}` - Agora retorna `cliente` e `attachments`
- `POST /api/tickets` - Aceita `cliente_id` e `user_id` (admin/support)
- `PUT /api/tickets/{ticket}` - Aceita `cliente_id` e `user_id` (admin/support)
- `GET /api/users-stats` - Inclui contagem de clientes
- `POST /api/users` - Aceita role `"cliente"`

### 🔐 Permissões Atualizadas
- Clientes só veem seus próprios tickets
- Clientes não podem editar/deletar tickets
- Clientes não podem deletar anexos
- Admin/Support podem gerenciar `cliente_id` e `user_id`

---

## 📝 Notas Importantes

1. **Clientes:** A API automaticamente filtra tickets por `cliente_id` quando o usuário é cliente
2. **Anexos:** Use a propriedade `url` do anexo para exibir imagens diretamente
3. **Upload:** Envie arquivos como `FormData` com campo `arquivos[]` (array)
4. **Filtros:** Use `cliente_id` para filtrar tickets de um cliente específico (admin/support)

---

## 🔗 Documentação Adicional

- `FRONTEND_API_CHANGES.md` - Guia completo de mudanças
- `ATTACHMENTS_API.md` - Documentação detalhada de anexos
- `API_RESPONSE_EXAMPLES.json` - Exemplos de respostas JSON

