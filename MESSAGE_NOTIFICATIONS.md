# Notificações de Mensagens em Chamados

## 📋 Visão Geral

O sistema de notificações de mensagens garante que quando uma mensagem é enviada em um chamado, os participantes relevantes sejam notificados tanto no sistema (banco de dados) quanto por email.

## 🔔 Como Funciona

### Quando Admin/Support/Assistant envia mensagem:

1. **Cliente é notificado** (se o chamado tiver `cliente_id`)
   - Notificação salva no banco de dados
   - Email enviado automaticamente
   - Não notifica se a mensagem for interna (`is_internal: true`)

### Quando Cliente envia mensagem:

1. **Usuário atribuído ao chamado é notificado** (se o chamado tiver `user_id`)
   - Notificação salva no banco de dados
   - Email enviado automaticamente

2. **Todos os Admins são notificados**
   - Notificação salva no banco de dados
   - Email enviado automaticamente
   - Não notifica o próprio admin se ele já foi notificado como usuário atribuído

## 📊 Estrutura da Notificação no Banco

As notificações são salvas na tabela `notifications` com os seguintes dados:

```json
{
  "ticket_id": 5,
  "ticket_title": "Problema no sistema",
  "ticket_status": "aberto",
  "ticket_priority": "alta",
  "message_id": 10,
  "sender_id": 2,
  "sender_name": "João Silva",
  "sender_email": "joao@exemplo.com",
  "sender_role": "admin",
  "message_preview": "Olá, como posso ajudar?",
  "message_full": "Olá, como posso ajudar? Preciso de mais informações...",
  "recipient_role": "cliente",
  "has_attachments": false,
  "message": "Você recebeu uma nova mensagem no chamado #5 de João Silva"
}
```

## 🔒 Regras de Notificação

### Mensagens Internas
- **Não geram notificações** para clientes
- Apenas admin/support veem mensagens internas
- Notificações internas podem ser criadas manualmente se necessário

### Prevenção de Auto-Notificação
- O sistema **não notifica o próprio remetente**
- Se admin envia mensagem, ele não recebe notificação
- Se cliente envia mensagem, ele não recebe notificação

### Múltiplos Admins
- Quando cliente envia mensagem, **todos os admins** são notificados
- Isso garante que nenhum admin perca mensagens importantes

## 📧 Email de Notificação

O email enviado contém:
- Informações do chamado (título, número, status)
- Nome do remetente
- Conteúdo da mensagem
- Link direto para visualizar o chamado
- Informações sobre anexos (se houver)

## 🔍 Consultar Notificações

As notificações podem ser consultadas através da API:

**GET** `/api/notifications`

Retorna todas as notificações do usuário logado, incluindo:
- Notificações de chamados atribuídos
- Notificações de mensagens em chamados
- Status de leitura (`read_at`)

## 💻 Exemplo de Uso no Frontend

```javascript
// Verificar notificações não lidas
async function getUnreadNotifications() {
  const response = await fetch('/api/notifications/unread', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  return await response.json();
}

// Marcar notificação como lida
async function markAsRead(notificationId) {
  const response = await fetch(`/api/notifications/${notificationId}/read`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  return await response.json();
}

// Filtrar notificações de mensagens
const notifications = await getUnreadNotifications();
const messageNotifications = notifications.filter(
  notif => notif.type === 'App\\Notifications\\TicketMessageNotification'
);
```

## ✅ Checklist de Funcionalidades

- [x] Notificações salvas no banco de dados
- [x] Emails enviados automaticamente
- [x] Cliente notificado quando admin envia mensagem
- [x] Admin notificado quando cliente envia mensagem
- [x] Usuário atribuído notificado quando cliente envia mensagem
- [x] Prevenção de auto-notificação
- [x] Mensagens internas não notificam clientes
- [x] Múltiplos admins notificados quando cliente envia mensagem
- [x] Tratamento de erros sem interromper o processo

---

**Última atualização:** 2025-11-17

