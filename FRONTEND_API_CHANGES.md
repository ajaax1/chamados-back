# 📋 Mudanças na API - Guia para Frontend

## 🆕 Novas Funcionalidades

### 1. Role "Cliente" 
- Novo tipo de usuário que só pode ver seus próprios chamados
- Clientes não podem editar ou deletar tickets/anexos

### 2. Campo `cliente_id` nos Tickets
- Tickets agora têm um dono (cliente) e um atendente (user_id)
- Permite rastrear de quem é o chamado e quem está atendendo

### 3. Sistema de Anexos
- Upload de múltiplos arquivos (PDFs, imagens, documentos)
- Download e visualização de arquivos
- Gerenciamento completo de anexos

---

## 🔄 Mudanças nas Rotas Existentes

### GET `/api/tickets-filtro`
**Mudanças:**
- Agora retorna `cliente` (objeto do usuário cliente) além de `user` (atendente)
- Retorna `attachments` (array de anexos) em cada ticket
- Clientes só veem seus próprios tickets automaticamente

**Novo formato de resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Título do chamado",
      "nome_cliente": "João Silva",
      "whatsapp_numero": "5511999999999",
      "descricao": "Descrição...",
      "status": "aberto",
      "priority": "alta",
      "user_id": 2,
      "cliente_id": 5,
      "user": {
        "id": 2,
        "name": "Atendente",
        "email": "atendente@email.com",
        "role": "support"
      },
      "cliente": {
        "id": 5,
        "name": "João Silva",
        "email": "joao@email.com",
        "role": "cliente"
      },
      "attachments": [
        {
          "id": 1,
          "nome_arquivo": "documento.pdf",
          "url": "http://localhost/storage/tickets/1/uuid.pdf",
          "tipo_mime": "application/pdf",
          "tamanho": 1024000
        }
      ],
      "created_at": "2025-11-13T00:00:00.000000Z",
      "updated_at": "2025-11-13T00:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### GET `/api/ticket/{id}`
**Mudanças:**
- Agora retorna `cliente` e `attachments` além dos campos anteriores

**Novo formato:**
```json
{
  "id": 1,
  "title": "Título",
  "nome_cliente": "João",
  "descricao": "...",
  "status": "aberto",
  "priority": "alta",
  "user_id": 2,
  "cliente_id": 5,
  "user": {...},
  "cliente": {...},
  "messages": [...],
  "attachments": [...]
}
```

### POST `/api/tickets`
**Mudanças:**
- Admin/Support podem definir `cliente_id` e `user_id`
- Clientes automaticamente têm `cliente_id` definido como seu próprio ID
- Retorna `cliente` e `attachments` na resposta

**Novo body (opcional para admin/support):**
```json
{
  "title": "Título",
  "nome_cliente": "João",
  "descricao": "...",
  "status": "aberto",
  "priority": "alta",
  "cliente_id": 5,  // NOVO - apenas admin/support
  "user_id": 2      // NOVO - apenas admin/support
}
```

### PUT `/api/tickets/{ticket}`
**Mudanças:**
- Admin/Support podem alterar `cliente_id` e `user_id`
- Clientes NÃO podem editar tickets
- Retorna `cliente` e `attachments` na resposta

**Novo body (opcional para admin/support):**
```json
{
  "title": "Título atualizado",
  "cliente_id": 6,  // NOVO - apenas admin/support
  "user_id": 3      // NOVO - apenas admin/support
}
```

### GET `/api/users-stats`
**Mudanças:**
- Agora inclui contagem de clientes

**Novo formato:**
```json
{
  "total": 10,
  "admins": 1,
  "support": 2,
  "assistant": 3,
  "cliente": 4  // NOVO
}
```

### POST `/api/users`
**Mudanças:**
- Agora aceita `role: "cliente"` como opção válida

**Body:**
```json
{
  "name": "Cliente Teste",
  "email": "cliente@email.com",
  "password": "senha123",
  "role": "cliente"  // NOVO - agora aceita "cliente"
}
```

---

## 🆕 Novas Rotas

### 1. Upload de Anexos
**POST** `/api/tickets/{ticket}/attachments`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body (FormData):**
```
arquivos[]: File (múltiplos arquivos)
```

**Resposta (201):**
```json
{
  "message": "Arquivos enviados com sucesso",
  "anexos": [
    {
      "id": 1,
      "ticket_id": 5,
      "nome_arquivo": "documento.pdf",
      "caminho_arquivo": "tickets/5/uuid.pdf",
      "tipo_mime": "application/pdf",
      "tamanho": 1024000,
      "url": "http://localhost/storage/tickets/5/uuid.pdf",
      "created_at": "2025-11-13T00:00:00.000000Z",
      "updated_at": "2025-11-13T00:00:00.000000Z"
    }
  ]
}
```

**Validações:**
- Máximo 10 arquivos por upload
- Tipos permitidos: jpeg, jpg, png, gif, pdf, doc, docx
- Tamanho máximo: 10MB por arquivo

### 2. Listar Anexos
**GET** `/api/tickets/{ticket}/attachments`

**Resposta (200):**
```json
[
  {
    "id": 1,
    "ticket_id": 5,
    "nome_arquivo": "documento.pdf",
    "caminho_arquivo": "tickets/5/uuid.pdf",
    "tipo_mime": "application/pdf",
    "tamanho": 1024000,
    "url": "http://localhost/storage/tickets/5/uuid.pdf",
    "created_at": "2025-11-13T00:00:00.000000Z",
    "updated_at": "2025-11-13T00:00:00.000000Z"
  }
]
```

### 3. Visualizar Arquivo
**GET** `/api/attachments/{attachment}`

Retorna o arquivo para visualização no navegador (imagens e PDFs).

**Nota:** Para imagens, use diretamente a propriedade `url` do anexo.

### 4. Download de Arquivo
**GET** `/api/attachments/{attachment}/download`

Faz download do arquivo.

### 5. Deletar Anexo
**DELETE** `/api/attachments/{attachment}`

**Resposta (200):**
```json
{
  "message": "Anexo deletado com sucesso"
}
```

**Permissões:** Clientes NÃO podem deletar anexos.

---

## 📝 Novos Campos nos Modelos

### Ticket
```typescript
interface Ticket {
  id: number;
  title: string;
  nome_cliente: string;
  whatsapp_numero?: string;
  descricao: string;
  status: 'aberto' | 'pendente' | 'resolvido' | 'finalizado';
  priority: 'baixa' | 'média' | 'alta';
  user_id?: number;        // Atendente
  cliente_id?: number;    // NOVO - Dono do chamado
  user?: User;            // Objeto do atendente
  cliente?: User;         // NOVO - Objeto do cliente
  attachments?: Attachment[]; // NOVO - Array de anexos
  messages?: Message[];
  created_at: string;
  updated_at: string;
}
```

### User
```typescript
interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'support' | 'assistant' | 'cliente'; // NOVO: 'cliente'
  // ... outros campos
}
```

### Attachment (Novo)
```typescript
interface Attachment {
  id: number;
  ticket_id: number;
  nome_arquivo: string;
  caminho_arquivo: string;
  tipo_mime: string;
  tamanho: number;
  url: string; // URL completa para acesso público
  created_at: string;
  updated_at: string;
}
```

---

## 🔐 Mudanças nas Permissões

### Clientes
- ✅ Podem criar tickets (automaticamente atribuídos a eles)
- ✅ Podem ver apenas seus próprios tickets
- ✅ Podem visualizar e enviar mensagens nos seus tickets
- ✅ Podem fazer upload de anexos nos seus tickets
- ❌ NÃO podem editar tickets
- ❌ NÃO podem deletar tickets
- ❌ NÃO podem deletar anexos

### Admin/Support
- ✅ Podem ver todos os tickets
- ✅ Podem definir `cliente_id` e `user_id` ao criar/editar tickets
- ✅ Podem gerenciar todos os tickets e anexos

### Assistant
- ✅ Podem ver apenas tickets atribuídos a eles (`user_id`)
- ✅ Podem editar tickets atribuídos a eles
- ✅ Podem gerenciar anexos dos tickets atribuídos a eles

---

## 💻 Exemplos de Código para Frontend

### TypeScript Interfaces
```typescript
// types/ticket.ts
export interface Ticket {
  id: number;
  title: string;
  nome_cliente: string;
  whatsapp_numero?: string;
  descricao: string;
  status: 'aberto' | 'pendente' | 'resolvido' | 'finalizado';
  priority: 'baixa' | 'média' | 'alta';
  user_id?: number;
  cliente_id?: number;
  user?: User;
  cliente?: User;
  attachments?: Attachment[];
  messages?: Message[];
  created_at: string;
  updated_at: string;
}

export interface Attachment {
  id: number;
  ticket_id: number;
  nome_arquivo: string;
  caminho_arquivo: string;
  tipo_mime: string;
  tamanho: number;
  url: string;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'support' | 'assistant' | 'cliente';
}
```

### Upload de Anexos (Next.js)
```typescript
const uploadAttachments = async (
  ticketId: number, 
  files: File[], 
  token: string
) => {
  const formData = new FormData();
  files.forEach(file => formData.append('arquivos[]', file));

  const response = await fetch(
    `${API_URL}/tickets/${ticketId}/attachments`,
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
      body: formData,
    }
  );

  if (!response.ok) {
    throw new Error('Erro ao enviar arquivos');
  }

  return response.json();
};
```

### Criar Ticket com Cliente (Admin/Support)
```typescript
const createTicket = async (ticketData: {
  title: string;
  nome_cliente: string;
  descricao: string;
  status: string;
  priority: string;
  cliente_id?: number;  // NOVO - opcional para admin/support
  user_id?: number;    // NOVO - opcional para admin/support
}, token: string) => {
  const response = await fetch(`${API_URL}/tickets`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(ticketData),
  });

  return response.json();
};
```

### Filtrar Tickets por Cliente
```typescript
// Admin/Support podem filtrar por cliente_id
const getTicketsByCliente = async (clienteId: number, token: string) => {
  const response = await fetch(
    `${API_URL}/tickets-filtro?cliente_id=${clienteId}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    }
  );

  return response.json();
};
```

### Verificar se Usuário é Cliente
```typescript
const isCliente = (user: User | null): boolean => {
  return user?.role === 'cliente';
};

// Exemplo de uso
if (isCliente(currentUser)) {
  // Cliente só vê seus próprios tickets automaticamente
  // Não precisa filtrar, a API já faz isso
}
```

### Exibir Anexos
```typescript
const AttachmentList = ({ attachments }: { attachments: Attachment[] }) => {
  const isImage = (mimeType: string) => mimeType.startsWith('image/');

  return (
    <div className="grid grid-cols-3 gap-4">
      {attachments.map((attachment) => (
        <div key={attachment.id} className="border rounded p-4">
          {isImage(attachment.tipo_mime) ? (
            <img
              src={attachment.url}
              alt={attachment.nome_arquivo}
              className="w-full h-48 object-cover"
            />
          ) : (
            <div className="w-full h-48 bg-gray-200 flex items-center justify-center">
              <span>📄 {attachment.nome_arquivo}</span>
            </div>
          )}
          <p className="text-sm mt-2">{attachment.nome_arquivo}</p>
          <a
            href={attachment.url}
            target="_blank"
            rel="noopener noreferrer"
            className="text-blue-500 text-sm"
          >
            Abrir
          </a>
        </div>
      ))}
    </div>
  );
};
```

---

## ⚠️ Breaking Changes

### 1. Tickets agora têm `cliente_id`
- Se você estava usando apenas `user_id`, agora precisa considerar `cliente_id` também
- `user_id` = atendente responsável
- `cliente_id` = dono do chamado

### 2. Clientes têm comportamento diferente
- Clientes automaticamente veem apenas seus próprios tickets
- Não é possível para clientes ver tickets de outros clientes
- Clientes não podem editar/deletar tickets

### 3. Novos campos obrigatórios
- Nenhum campo novo é obrigatório, mas `cliente_id` é automaticamente definido para clientes

---

## 🚀 Checklist de Migração

- [ ] Atualizar interfaces TypeScript com novos campos
- [ ] Adicionar campo `cliente` na exibição de tickets
- [ ] Implementar upload de anexos
- [ ] Adicionar visualização de anexos nos tickets
- [ ] Atualizar formulário de criação de tickets (adicionar seleção de cliente para admin/support)
- [ ] Atualizar formulário de edição de tickets (adicionar seleção de cliente para admin/support)
- [ ] Implementar restrições de UI para clientes (esconder botões de editar/deletar)
- [ ] Atualizar filtros para incluir `cliente_id`
- [ ] Testar permissões de cada role
- [ ] Atualizar estatísticas para incluir contagem de clientes

---

## 📞 Suporte

Para dúvidas sobre a API, consulte:
- `ATTACHMENTS_API.md` - Documentação completa de anexos
- `ROLES_SYSTEM.md` - Sistema de roles e permissões (se existir)

