# Sistema de Mensagens Internas - API de Chamados

## 📋 Visão Geral

O sistema de mensagens internas permite que administradores, suporte e clientes troquem mensagens diretamente dentro dos chamados, sem depender do WhatsApp. Todas as mensagens são armazenadas no banco de dados e notificações por email são enviadas automaticamente.

## 🔐 Autenticação

Todas as rotas requerem autenticação via token Sanctum:
```
Authorization: Bearer {seu_token_aqui}
```

## 📡 Endpoints

### 1. Listar Mensagens Internas de um Chamado

**GET** `/api/tickets/{ticket}/messages-internal`

Lista todas as mensagens internas de um chamado específico.

**Permissões:**
- Usuário deve ter permissão para visualizar o chamado
- Clientes só veem mensagens não-internas
- Admin/Support veem todas as mensagens

**Resposta de Sucesso (200):**
```json
[
  {
    "id": 1,
    "ticket_id": 5,
    "user_id": 2,
    "message": "Olá, como posso ajudar?",
    "is_internal": false,
    "created_at": "2025-11-17T14:30:00.000000Z",
    "updated_at": "2025-11-17T14:30:00.000000Z",
    "user": {
      "id": 2,
      "name": "João Silva",
      "email": "joao@exemplo.com",
      "role": "admin"
    }
  },
  {
    "id": 2,
    "ticket_id": 5,
    "user_id": 3,
    "message": "Preciso de ajuda com o sistema",
    "is_internal": false,
    "created_at": "2025-11-17T14:35:00.000000Z",
    "updated_at": "2025-11-17T14:35:00.000000Z",
    "user": {
      "id": 3,
      "name": "Maria Santos",
      "email": "maria@exemplo.com",
      "role": "cliente"
    }
  }
]
```

**Resposta de Erro (403):**
```json
{
  "message": "Acesso negado. Você não tem permissão para ver este chamado."
}
```

---

### 2. Enviar Mensagem Interna

**POST** `/api/tickets/{ticket}/messages-internal`

Envia uma nova mensagem interna no chamado.

**Permissões:**
- Usuário deve ter permissão para visualizar o chamado
- Clientes não podem enviar mensagens internas (`is_internal: true`)

**Body (FormData ou JSON):**
```json
{
  "message": "Olá, como posso ajudar?",
  "is_internal": false,
  "anexos": [arquivo1, arquivo2, ...]
}
```

**Campos:**
- `message` (string, obrigatório, máximo 5000 caracteres): Conteúdo da mensagem
- `is_internal` (boolean, opcional, padrão: false): Se `true`, mensagem visível apenas para admin/support
- `anexos` (array de arquivos, opcional, máximo 10 arquivos): Arquivos anexados à mensagem
  - Tipos permitidos: jpeg, jpg, png, gif, webp, pdf, doc, docx, xls, xlsx, txt
  - Tamanho máximo: 10MB por arquivo

**Resposta de Sucesso (201):**
```json
{
  "message": {
    "id": 1,
    "ticket_id": 5,
    "user_id": 2,
    "message": "Olá, como posso ajudar?",
    "is_internal": false,
    "created_at": "2025-11-17T14:30:00.000000Z",
    "updated_at": "2025-11-17T14:30:00.000000Z",
    "user": {
      "id": 2,
      "name": "João Silva",
      "email": "joao@exemplo.com",
      "role": "admin"
    },
    "attachments": [
      {
        "id": 1,
        "ticket_message_id": 1,
        "nome_arquivo": "documento.pdf",
        "caminho_arquivo": "messages/5/1/uuid.pdf",
        "tipo_mime": "application/pdf",
        "tamanho": 1024000,
        "url": "http://localhost/storage/messages/5/1/uuid.pdf",
        "created_at": "2025-11-17T14:30:00.000000Z"
      }
    ]
  },
  "attachments": [
    {
      "id": 1,
      "ticket_message_id": 1,
      "nome_arquivo": "documento.pdf",
      "caminho_arquivo": "messages/5/1/uuid.pdf",
      "tipo_mime": "application/pdf",
      "tamanho": 1024000,
      "url": "http://localhost/storage/messages/5/1/uuid.pdf"
    }
  ]
}
```

**Respostas de Erro:**

**403 - Sem permissão:**
```json
{
  "message": "Acesso negado. Você não tem permissão para enviar mensagens neste chamado."
}
```

**403 - Cliente tentando enviar mensagem interna:**
```json
{
  "message": "Clientes não podem enviar mensagens internas."
}
```

**422 - Validação:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "message": [
      "The message field is required."
    ]
  }
}
```

---

## 🔔 Notificações por Email

Quando uma nova mensagem é enviada, o sistema envia automaticamente um email de notificação:

- **Admin/Support envia mensagem** → Cliente recebe notificação por email
- **Cliente envia mensagem** → Admin/Support atribuído e Admin recebem notificação por email
- **Mensagens internas** (`is_internal: true`) → Não geram notificações para clientes

O email segue o mesmo padrão visual dos outros emails do sistema e inclui:
- Informações do chamado
- Conteúdo da mensagem
- Link direto para visualizar e responder no sistema

---

## 📝 Exemplos de Uso

### JavaScript/TypeScript

```javascript
// Listar mensagens de um chamado
async function getTicketMessages(ticketId) {
  const response = await fetch(`/api/tickets/${ticketId}/messages-internal`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  if (!response.ok) {
    throw new Error('Erro ao buscar mensagens');
  }
  
  return await response.json();
}

// Enviar mensagem com anexos
async function sendMessage(ticketId, message, isInternal = false, files = []) {
  const formData = new FormData();
  formData.append('message', message);
  formData.append('is_internal', isInternal);
  
  // Adicionar arquivos ao FormData
  files.forEach((file, index) => {
    formData.append(`anexos[${index}]`, file);
  });
  
  const response = await fetch(`/api/tickets/${ticketId}/messages-internal`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
      // Não definir Content-Type, o navegador fará isso automaticamente com FormData
    },
    body: formData
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Erro ao enviar mensagem');
  }
  
  return await response.json();
}

// Exemplo de uso
const messages = await getTicketMessages(5);
console.log('Mensagens:', messages);

await sendMessage(5, 'Olá, como posso ajudar?', false);
```

### cURL

```bash
# Listar mensagens
curl -X GET "http://localhost:8000/api/tickets/5/messages-internal" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"

# Enviar mensagem
curl -X POST "http://localhost:8000/api/tickets/5/messages-internal" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Olá, como posso ajudar?",
    "is_internal": false
  }'

# Enviar mensagem com anexos
curl -X POST "http://localhost:8000/api/tickets/5/messages-internal" \
  -H "Authorization: Bearer {token}" \
  -F "message=Olá, segue o arquivo anexo" \
  -F "is_internal=false" \
  -F "anexos[]=@/caminho/para/arquivo1.pdf" \
  -F "anexos[]=@/caminho/para/arquivo2.jpg"
```

---

### 3. Visualizar Anexo de Mensagem

**GET** `/api/message-attachments/{attachment}`

Visualiza um anexo de mensagem no navegador (útil para imagens e PDFs).

**Permissões:**
- Usuário deve ter permissão para visualizar o chamado relacionado

**Resposta:** Arquivo renderizado no navegador

---

### 4. Download de Anexo de Mensagem

**GET** `/api/message-attachments/{attachment}/download`

Faz download de um anexo de mensagem.

**Permissões:**
- Usuário deve ter permissão para visualizar o chamado relacionado

**Resposta:** Arquivo para download

---

## 🔒 Regras de Permissão

### Visualização de Mensagens

- **Admin/Support**: Veem todas as mensagens (incluindo internas)
- **Assistant**: Veem apenas mensagens não-internas dos tickets atribuídos a eles
- **Cliente**: Veem apenas mensagens não-internas dos seus próprios tickets

### Envio de Mensagens

- **Admin/Support**: Podem enviar mensagens normais e internas
- **Assistant**: Podem enviar mensagens normais (não-internas)
- **Cliente**: Podem enviar apenas mensagens normais (não-internas)

---

## 📊 Estrutura do Banco de Dados

### Tabela: `ticket_messages`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | ID único da mensagem |
| `ticket_id` | bigint | ID do chamado (FK) |
| `user_id` | bigint | ID do usuário que enviou (FK) |
| `message` | text | Conteúdo da mensagem |
| `is_internal` | boolean | Se true, mensagem interna apenas para admin/support |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

### Tabela: `message_attachments`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | ID único do anexo |
| `ticket_message_id` | bigint | ID da mensagem (FK) |
| `nome_arquivo` | string | Nome original do arquivo |
| `caminho_arquivo` | string | Caminho relativo no storage |
| `tipo_mime` | string | Tipo MIME do arquivo (ex: image/jpeg, application/pdf) |
| `tamanho` | bigint | Tamanho em bytes |
| `created_at` | timestamp | Data de criação |
| `updated_at` | timestamp | Data de atualização |

**Atributos calculados:**
- `url`: URL pública do arquivo (acessível via `storage/`)

---

## 🎯 Diferença entre Mensagens WhatsApp e Mensagens Internas

### Mensagens WhatsApp (`/api/tickets/{ticket}/messages`)
- Enviadas via WhatsApp através do sistema
- Armazenadas na tabela `whatsapp_messages`
- Usadas para comunicação externa

### Mensagens Internas (`/api/tickets/{ticket}/messages-internal`)
- Enviadas diretamente no sistema
- Armazenadas na tabela `ticket_messages`
- Usadas para comunicação interna entre usuários do sistema
- Geram notificações por email automaticamente

---

## ✅ Checklist de Implementação

- [x] Migration criada e executada
- [x] Model `TicketMessage` criado
- [x] Controller atualizado com métodos `indexInternal` e `storeInternal`
- [x] Rotas adicionadas em `api.php`
- [x] Notificação por email implementada
- [x] Template de email criado com mesmo estilo visual
- [x] Permissões e validações implementadas
- [x] Relacionamento no modelo `Ticket` adicionado

---

## 📞 Suporte

Para dúvidas ou problemas, consulte a documentação principal da API ou entre em contato com a equipe de desenvolvimento.

