# API de Notificações - Documentação para Frontend

## Visão Geral

O sistema de notificações permite que usuários recebam notificações quando eventos importantes acontecem, como quando um chamado é atribuído a eles. As notificações são armazenadas no banco de dados e podem ser acessadas via API.

## Como Funciona

### Estrutura de Notificações

As notificações são armazenadas na tabela `notifications` do banco de dados. Cada notificação contém:
- `id`: UUID único da notificação
- `type`: Tipo da notificação (ex: `App\Notifications\TicketAssignedNotification`)
- `notifiable_id`: ID do usuário que recebeu a notificação
- `notifiable_type`: Tipo do modelo (geralmente `App\Models\User`)
- `data`: JSON com os dados da notificação
- `read_at`: Timestamp de quando foi lida (null se não lida)
- `created_at`: Data de criação
- `updated_at`: Data de atualização

### Dados da Notificação

O campo `data` contém um objeto JSON com informações sobre a notificação:

```json
{
  "ticket_id": 1,
  "ticket_title": "Problema com login",
  "ticket_status": "aberto",
  "ticket_priority": "alta",
  "assigned_type": "user",
  "message": "Um novo chamado foi atribuído a você: Problema com login"
}
```

## Rotas da API

Todas as rotas requerem autenticação via token Sanctum.

### 1. Listar Todas as Notificações

**GET** `/api/notifications`

Retorna todas as notificações do usuário autenticado (lidas e não lidas).

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "type": "App\\Notifications\\TicketAssignedNotification",
      "notifiable_type": "App\\Models\\User",
      "notifiable_id": 1,
      "data": {
        "ticket_id": 5,
        "ticket_title": "Problema com login",
        "ticket_status": "aberto",
        "ticket_priority": "alta",
        "assigned_type": "user",
        "message": "Um novo chamado foi atribuído a você: Problema com login"
      },
      "read_at": null,
      "created_at": "2025-11-13T10:30:00.000000Z",
      "updated_at": "2025-11-13T10:30:00.000000Z"
    }
  ],
  "per_page": 20,
  "total": 1
}
```

**Exemplo (TypeScript/Next.js):**
```typescript
const getNotifications = async () => {
  const response = await fetch(`${API_URL}/notifications`, {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return response.json();
};
```

---

### 2. Listar Apenas Notificações Não Lidas

**GET** `/api/notifications/unread`

Retorna apenas as notificações que ainda não foram lidas pelo usuário.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "type": "App\\Notifications\\TicketAssignedNotification",
      "notifiable_type": "App\\Models\\User",
      "notifiable_id": 1,
      "data": {
        "ticket_id": 5,
        "ticket_title": "Problema com login",
        "ticket_status": "aberto",
        "ticket_priority": "alta",
        "assigned_type": "user",
        "message": "Um novo chamado foi atribuído a você: Problema com login"
      },
      "read_at": null,
      "created_at": "2025-11-13T10:30:00.000000Z",
      "updated_at": "2025-11-13T10:30:00.000000Z"
    }
  ],
  "per_page": 20,
  "total": 1
}
```

**Exemplo (TypeScript/Next.js):**
```typescript
const getUnreadNotifications = async () => {
  const response = await fetch(`${API_URL}/notifications/unread`, {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return response.json();
};
```

---

### 3. Contar Notificações Não Lidas

**GET** `/api/notifications/count`

Retorna apenas o número de notificações não lidas. Útil para exibir um badge com a contagem.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "count": 3
}
```

**Exemplo (TypeScript/Next.js):**
```typescript
const getUnreadCount = async () => {
  const response = await fetch(`${API_URL}/notifications/count`, {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  const data = await response.json();
  return data.count; // Retorna apenas o número
};
```

---

### 4. Marcar Notificação como Lida

**POST** `/api/notifications/{id}/read`

Marca uma notificação específica como lida.

**Headers:**
```
Authorization: Bearer {token}
```

**Parâmetros:**
- `id` (path): UUID da notificação

**Resposta (200):**
```json
{
  "message": "Notificação marcada como lida"
}
```

**Resposta (404) - Notificação não encontrada:**
```json
{
  "message": "Notificação não encontrada"
}
```

**Exemplo (TypeScript/Next.js):**
```typescript
const markAsRead = async (notificationId: string) => {
  const response = await fetch(`${API_URL}/notifications/${notificationId}/read`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return response.json();
};
```

---

### 5. Marcar Todas as Notificações como Lidas

**POST** `/api/notifications/read-all`

Marca todas as notificações não lidas do usuário como lidas.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "message": "Todas as notificações foram marcadas como lidas"
}
```

**Exemplo (TypeScript/Next.js):**
```typescript
const markAllAsRead = async () => {
  const response = await fetch(`${API_URL}/notifications/read-all`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return response.json();
};
```

---

### 6. Deletar Notificação

**DELETE** `/api/notifications/{id}`

Remove uma notificação do banco de dados.

**Headers:**
```
Authorization: Bearer {token}
```

**Parâmetros:**
- `id` (path): UUID da notificação

**Resposta (200):**
```json
{
  "message": "Notificação deletada"
}
```

**Resposta (404) - Notificação não encontrada:**
```json
{
  "message": "Notificação não encontrada"
}
```

**Exemplo (TypeScript/Next.js):**
```typescript
const deleteNotification = async (notificationId: string) => {
  const response = await fetch(`${API_URL}/notifications/${notificationId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  return response.json();
};
```

---

## Exemplos de Uso no Frontend

### Hook React para Gerenciar Notificações

```typescript
import { useState, useEffect } from 'react';

interface Notification {
  id: string;
  type: string;
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

export const useNotifications = (token: string) => {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(true);

  const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

  // Buscar notificações não lidas
  const fetchUnreadNotifications = async () => {
    try {
      const response = await fetch(`${API_URL}/notifications/unread`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      const data = await response.json();
      setNotifications(data.data || []);
    } catch (error) {
      console.error('Erro ao buscar notificações:', error);
    } finally {
      setLoading(false);
    }
  };

  // Buscar contagem de não lidas
  const fetchUnreadCount = async () => {
    try {
      const response = await fetch(`${API_URL}/notifications/count`, {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      const data = await response.json();
      setUnreadCount(data.count || 0);
    } catch (error) {
      console.error('Erro ao buscar contagem:', error);
    }
  };

  // Marcar como lida
  const markAsRead = async (notificationId: string) => {
    try {
      await fetch(`${API_URL}/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
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
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      
      // Atualizar estado local
      setNotifications(prev =>
        prev.map(notif => ({ ...notif, read_at: new Date().toISOString() }))
      );
      
      setUnreadCount(0);
    } catch (error) {
      console.error('Erro ao marcar todas como lidas:', error);
    }
  };

  // Deletar notificação
  const deleteNotification = async (notificationId: string) => {
    try {
      await fetch(`${API_URL}/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      
      // Remover do estado local
      setNotifications(prev => prev.filter(notif => notif.id !== notificationId));
      
      // Atualizar contagem
      fetchUnreadCount();
    } catch (error) {
      console.error('Erro ao deletar notificação:', error);
    }
  };

  useEffect(() => {
    if (token) {
      fetchUnreadNotifications();
      fetchUnreadCount();
      
      // Atualizar a cada 30 segundos
      const interval = setInterval(() => {
        fetchUnreadNotifications();
        fetchUnreadCount();
      }, 30000);
      
      return () => clearInterval(interval);
    }
  }, [token]);

  return {
    notifications,
    unreadCount,
    loading,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    refresh: fetchUnreadNotifications,
  };
};
```

### Componente de Notificações

```typescript
import { useNotifications } from '@/hooks/useNotifications';

export const NotificationBell = ({ token }: { token: string }) => {
  const {
    notifications,
    unreadCount,
    markAsRead,
    markAllAsRead,
  } = useNotifications(token);

  const handleNotificationClick = (notification: Notification) => {
    // Marcar como lida
    markAsRead(notification.id);
    
    // Navegar para o ticket
    const ticketId = notification.data.ticket_id;
    window.location.href = `/tickets/${ticketId}`;
  };

  return (
    <div className="notification-container">
      <div className="notification-bell">
        🔔
        {unreadCount > 0 && (
          <span className="badge">{unreadCount}</span>
        )}
      </div>
      
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
                onClick={() => handleNotificationClick(notification)}
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
    </div>
  );
};
```

## Quando as Notificações São Criadas

As notificações são criadas automaticamente pelo backend quando:

1. **Um chamado é criado e atribuído a um usuário** (`user_id`)
2. **Um chamado é criado para um cliente** (`cliente_id`)
3. **Um chamado é atualizado e o usuário atribuído muda** (`user_id` alterado)
4. **Um chamado é atualizado e o cliente muda** (`cliente_id` alterado)
5. **Todos os admins são notificados quando um novo chamado é criado**

## Tipos de Notificações

### TicketAssignedNotification

Notificação enviada quando um chamado é atribuído a um usuário.

**Dados:**
- `ticket_id`: ID do chamado
- `ticket_title`: Título do chamado
- `ticket_status`: Status do chamado (aberto, pendente, resolvido, finalizado)
- `ticket_priority`: Prioridade (baixa, média, alta)
- `assigned_type`: Tipo de atribuição ('user' ou 'cliente')
- `message`: Mensagem descritiva da notificação

## Códigos de Resposta

| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 401 | Não autenticado (token inválido ou ausente) |
| 404 | Notificação não encontrada |
| 500 | Erro interno do servidor |

## Dicas de Implementação

1. **Polling**: Atualize as notificações periodicamente (a cada 30-60 segundos)
2. **Badge**: Use a rota `/notifications/count` para exibir um badge com o número de não lidas
3. **Auto-marcar como lida**: Quando o usuário clicar em uma notificação, marque como lida automaticamente
4. **Cache**: Considere cachear as notificações no frontend para melhor performance
5. **WebSockets (futuro)**: Para notificações em tempo real, considere implementar WebSockets

## Exemplo de Resposta Completa

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "type": "App\\Notifications\\TicketAssignedNotification",
  "notifiable_type": "App\\Models\\User",
  "notifiable_id": 1,
  "data": {
    "ticket_id": 5,
    "ticket_title": "Problema com login no sistema",
    "ticket_status": "aberto",
    "ticket_priority": "alta",
    "assigned_type": "user",
    "message": "Um novo chamado foi atribuído a você: Problema com login no sistema"
  },
  "read_at": null,
  "created_at": "2025-11-13T10:30:00.000000Z",
  "updated_at": "2025-11-13T10:30:00.000000Z"
}
```

