# Guia de Notificações para Frontend

## 📋 Visão Geral

O sistema de notificações informa os usuários quando um chamado é atribuído a eles. As notificações são criadas automaticamente pelo backend e podem ser consultadas via API.

## 🔔 Quando as Notificações São Criadas

As notificações são criadas automaticamente quando:

1. **Um novo chamado é criado** → Notifica:
   - O usuário atribuído (`user_id`)
   - O cliente (`cliente_id`)
   - Todos os administradores

2. **Um chamado é atualizado e o usuário/cliente muda** → Notifica:
   - O novo usuário atribuído (se `user_id` mudou)
   - O novo cliente (se `cliente_id` mudou)

## 🛣️ Rotas Disponíveis

Todas as rotas precisam do token de autenticação no header:
```
Authorization: Bearer {seu_token}
```

### 1. Listar Todas as Notificações
```
GET /api/notifications
```
Retorna todas as notificações (lidas e não lidas) do usuário logado.

**Resposta:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "type": "App\\Notifications\\TicketAssignedNotification",
      "data": {
        "ticket_id": 5,
        "ticket_title": "Problema com login",
        "ticket_status": "aberto",
        "ticket_priority": "alta",
        "assigned_type": "user",
        "message": "Um novo chamado foi atribuído a você: Problema com login"
      },
      "read_at": null,
      "created_at": "2025-11-13T10:30:00.000000Z"
    }
  ],
  "per_page": 20,
  "total": 1
}
```

---

### 2. Listar Apenas Não Lidas
```
GET /api/notifications/unread
```
Retorna apenas as notificações que ainda não foram lidas.

**Resposta:** Mesmo formato da rota anterior, mas apenas com `read_at: null`.

---

### 3. Contar Não Lidas
```
GET /api/notifications/count
```
Retorna apenas o número de notificações não lidas. **Use esta rota para exibir um badge!**

**Resposta:**
```json
{
  "count": 3
}
```

---

### 4. Marcar como Lida
```
POST /api/notifications/{id}/read
```
Marca uma notificação específica como lida.

**Resposta:**
```json
{
  "message": "Notificação marcada como lida"
}
```

---

### 5. Marcar Todas como Lidas
```
POST /api/notifications/read-all
```
Marca todas as notificações não lidas como lidas.

**Resposta:**
```json
{
  "message": "Todas as notificações foram marcadas como lidas"
}
```

---

### 6. Deletar Notificação
```
DELETE /api/notifications/{id}
```
Remove uma notificação do banco de dados.

**Resposta:**
```json
{
  "message": "Notificação deletada"
}
```

---

## 💻 Exemplos de Código

### Exemplo 1: Buscar Contagem de Não Lidas (para Badge)

```typescript
// Função para buscar contagem de não lidas
async function getUnreadCount(token: string): Promise<number> {
  const response = await fetch('http://seu-backend.com/api/notifications/count', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });
  
  const data = await response.json();
  return data.count; // Retorna o número (ex: 3)
}

// Uso no componente
const [unreadCount, setUnreadCount] = useState(0);

useEffect(() => {
  const fetchCount = async () => {
    const count = await getUnreadCount(token);
    setUnreadCount(count);
  };
  
  fetchCount();
  // Atualizar a cada 30 segundos
  const interval = setInterval(fetchCount, 30000);
  return () => clearInterval(interval);
}, [token]);
```

---

### Exemplo 2: Listar Notificações Não Lidas

```typescript
// Função para buscar notificações não lidas
async function getUnreadNotifications(token: string) {
  const response = await fetch('http://seu-backend.com/api/notifications/unread', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });
  
  return response.json();
}

// Uso
const [notifications, setNotifications] = useState([]);

useEffect(() => {
  const fetchNotifications = async () => {
    const data = await getUnreadNotifications(token);
    setNotifications(data.data); // data.data contém o array de notificações
  };
  
  fetchNotifications();
}, [token]);
```

---

### Exemplo 3: Marcar Notificação como Lida ao Clicar

```typescript
// Função para marcar como lida
async function markAsRead(notificationId: string, token: string) {
  const response = await fetch(
    `http://seu-backend.com/api/notifications/${notificationId}/read`,
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    }
  );
  
  return response.json();
}

// Uso ao clicar em uma notificação
const handleNotificationClick = async (notification: any) => {
  // Marcar como lida
  await markAsRead(notification.id, token);
  
  // Navegar para o ticket
  const ticketId = notification.data.ticket_id;
  router.push(`/tickets/${ticketId}`);
  
  // Atualizar lista de notificações
  refreshNotifications();
};
```

---

### Exemplo 4: Componente Completo de Notificações (React)

```typescript
import { useState, useEffect } from 'react';

interface Notification {
  id: string;
  data: {
    ticket_id: number;
    ticket_title: string;
    ticket_status: string;
    ticket_priority: string;
    assigned_type: string;
    message: string;
  };
  read_at: string | null;
  created_at: string;
}

export function NotificationBell({ token }: { token: string }) {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isOpen, setIsOpen] = useState(false);

  const API_URL = 'http://seu-backend.com/api';

  // Buscar contagem de não lidas
  const fetchUnreadCount = async () => {
    try {
      const response = await fetch(`${API_URL}/notifications/count`, {
        headers: { 'Authorization': `Bearer ${token}` },
      });
      const data = await response.json();
      setUnreadCount(data.count);
    } catch (error) {
      console.error('Erro ao buscar contagem:', error);
    }
  };

  // Buscar notificações não lidas
  const fetchNotifications = async () => {
    try {
      const response = await fetch(`${API_URL}/notifications/unread`, {
        headers: { 'Authorization': `Bearer ${token}` },
      });
      const data = await response.json();
      setNotifications(data.data || []);
    } catch (error) {
      console.error('Erro ao buscar notificações:', error);
    }
  };

  // Marcar como lida
  const markAsRead = async (notificationId: string) => {
    try {
      await fetch(`${API_URL}/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
      });
      
      // Atualizar estado local
      setNotifications(prev =>
        prev.map(notif =>
          notif.id === notificationId
            ? { ...notif, read_at: new Date().toISOString() }
            : notif
        )
      );
      
      // Atualizar contagem
      fetchUnreadCount();
    } catch (error) {
      console.error('Erro ao marcar como lida:', error);
    }
  };

  // Marcar todas como lidas
  const markAllAsRead = async () => {
    try {
      await fetch(`${API_URL}/notifications/read-all`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
      });
      
      setNotifications([]);
      setUnreadCount(0);
    } catch (error) {
      console.error('Erro ao marcar todas como lidas:', error);
    }
  };

  // Carregar dados ao montar e atualizar periodicamente
  useEffect(() => {
    fetchUnreadCount();
    fetchNotifications();
    
    const interval = setInterval(() => {
      fetchUnreadCount();
      fetchNotifications();
    }, 30000); // Atualizar a cada 30 segundos
    
    return () => clearInterval(interval);
  }, [token]);

  return (
    <div className="notification-container">
      {/* Botão do sino com badge */}
      <button onClick={() => setIsOpen(!isOpen)} className="notification-bell">
        🔔
        {unreadCount > 0 && (
          <span className="badge">{unreadCount}</span>
        )}
      </button>

      {/* Dropdown de notificações */}
      {isOpen && (
        <div className="notification-dropdown">
          <div className="notification-header">
            <h3>Notificações</h3>
            {unreadCount > 0 && (
              <button onClick={markAllAsRead}>
                Marcar todas como lidas
              </button>
            )}
          </div>

          <div className="notification-list">
            {notifications.length === 0 ? (
              <p>Nenhuma notificação</p>
            ) : (
              notifications.map(notification => (
                <div
                  key={notification.id}
                  className={`notification-item ${
                    !notification.read_at ? 'unread' : ''
                  }`}
                  onClick={() => {
                    markAsRead(notification.id);
                    // Navegar para o ticket
                    window.location.href = `/tickets/${notification.data.ticket_id}`;
                  }}
                >
                  <p>{notification.data.message}</p>
                  <small>
                    {new Date(notification.created_at).toLocaleString('pt-BR')}
                  </small>
                </div>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}
```

---

## 📊 Estrutura dos Dados

### Objeto de Notificação Completo

```typescript
interface Notification {
  id: string;                    // UUID da notificação
  type: string;                  // Tipo (ex: "App\\Notifications\\TicketAssignedNotification")
  notifiable_type: string;      // Sempre "App\\Models\\User"
  notifiable_id: number;        // ID do usuário
  data: {
    ticket_id: number;          // ID do chamado
    ticket_title: string;       // Título do chamado
    ticket_status: string;       // Status: "aberto", "pendente", "resolvido", "finalizado"
    ticket_priority: string;     // Prioridade: "baixa", "média", "alta"
    assigned_type: string;       // "user" ou "cliente"
    message: string;            // Mensagem descritiva
  };
  read_at: string | null;       // null se não lida, timestamp se lida
  created_at: string;           // Data de criação
  updated_at: string;           // Data de atualização
}
```

---

## 🎯 Fluxo Recomendado

1. **Ao carregar a página:**
   - Buscar contagem de não lidas (`GET /notifications/count`)
   - Exibir badge com o número

2. **Ao abrir o menu de notificações:**
   - Buscar notificações não lidas (`GET /notifications/unread`)
   - Exibir lista

3. **Ao clicar em uma notificação:**
   - Marcar como lida (`POST /notifications/{id}/read`)
   - Navegar para o ticket
   - Atualizar contagem

4. **Atualização automática:**
   - Usar `setInterval` para atualizar a cada 30-60 segundos
   - Ou usar WebSockets (se implementado no futuro)

---

## ⚠️ Dicas Importantes

1. **Performance:** Use a rota `/notifications/count` para o badge (é mais leve)
2. **Polling:** Atualize a cada 30-60 segundos, não mais frequente
3. **Auto-marcar:** Marque como lida automaticamente ao clicar
4. **Cache:** Considere cachear as notificações no estado local
5. **Erros:** Sempre trate erros (401 = token inválido, 404 = notificação não existe)

---

## 🔐 Autenticação

Todas as rotas requerem autenticação. Se receber erro 401, o token expirou ou é inválido:

```typescript
if (response.status === 401) {
  // Token inválido, redirecionar para login
  router.push('/login');
}
```

---

## 📝 Resumo das Rotas

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/notifications` | Listar todas |
| GET | `/api/notifications/unread` | Listar não lidas |
| GET | `/api/notifications/count` | Contar não lidas |
| POST | `/api/notifications/{id}/read` | Marcar como lida |
| POST | `/api/notifications/read-all` | Marcar todas como lidas |
| DELETE | `/api/notifications/{id}` | Deletar |

---

## 🚀 Exemplo de Integração Rápida

```typescript
// 1. Instalar dependências (se necessário)
// npm install axios

// 2. Criar serviço de notificações
import axios from 'axios';

const API_URL = 'http://seu-backend.com/api';

export const notificationService = {
  getCount: (token: string) =>
    axios.get(`${API_URL}/notifications/count`, {
      headers: { Authorization: `Bearer ${token}` },
    }),

  getUnread: (token: string) =>
    axios.get(`${API_URL}/notifications/unread`, {
      headers: { Authorization: `Bearer ${token}` },
    }),

  markAsRead: (id: string, token: string) =>
    axios.post(`${API_URL}/notifications/${id}/read`, {}, {
      headers: { Authorization: `Bearer ${token}` },
    }),

  markAllAsRead: (token: string) =>
    axios.post(`${API_URL}/notifications/read-all`, {}, {
      headers: { Authorization: `Bearer ${token}` },
    }),

  delete: (id: string, token: string) =>
    axios.delete(`${API_URL}/notifications/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    }),
};
```

Pronto! Agora você tem tudo que precisa para implementar notificações no frontend! 🎉

