# Exemplos de Uso da API de Tickets

## Autenticação
Todas as rotas (exceto login) requerem um token de autenticação no header:
```
Authorization: Bearer {seu_token_aqui}
```

## Rotas Disponíveis

### 1. Login
```bash
POST /api/login
Content-Type: application/json

{
    "email": "usuario@exemplo.com",
    "password": "senha123"
}
```

### 2. Listar Tickets (com filtros)
```bash
GET /api/tickets-filtro?search=termo&status=aberto&priority=alta&page=1
Authorization: Bearer {token}
```

### 3. Criar Ticket
```bash
POST /api/tickets
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Problema no sistema",
    "nome_cliente": "João Silva",
    "whatsapp_numero": "11999999999",
    "descricao": "Descrição do problema",
    "status": "aberto",
    "priority": "alta"
}
```

### 4. Visualizar Ticket
```bash
GET /api/tickets/{id}
Authorization: Bearer {token}
```

### 5. Atualizar Ticket
```bash
PUT /api/tickets/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Novo título",
    "nome_cliente": "João Silva",
    "whatsapp_numero": "11999999999",
    "descricao": "Nova descrição",
    "status": "resolvido",
    "priority": "média"
}
```

### 6. **DELETAR TICKET** ⚠️
```bash
DELETE /api/tickets/{id}
Authorization: Bearer {token}
```

**Resposta:**
```json
{
    "message": "Chamado excluído"
}
```

### 7. Estatísticas dos Tickets
```bash
GET /api/tickets-stats
Authorization: Bearer {token}
```

**Resposta:**
```json
{
    "total": 150,
    "abertos": 45,
    "resolvidos": 80,
    "pendentes": 25
}
```

## Exemplos de Frontend

### JavaScript - Deletar com Confirmação
```javascript
async function deleteTicket(ticketId) {
    // Confirmação antes de deletar
    if (!confirm('Tem certeza que deseja excluir este ticket? Esta ação não pode ser desfeita.')) {
        return;
    }

    try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/tickets/${ticketId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Erro ao excluir ticket');
        }

        const result = await response.json();
        console.log('Ticket excluído:', result);
        
        // Recarregar lista de tickets
        loadTickets();
        
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir ticket: ' + error.message);
    }
}
```

### React - Componente com Botão de Deletar
```jsx
import React, { useState } from 'react';

function TicketCard({ ticket, onDelete }) {
    const [showConfirm, setShowConfirm] = useState(false);

    const handleDelete = async () => {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`/api/tickets/${ticket.id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Erro ao excluir ticket');
            }

            onDelete(ticket.id);
            setShowConfirm(false);
            
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao excluir ticket: ' + error.message);
        }
    };

    return (
        <div className="card mb-3">
            <div className="card-body">
                <div className="d-flex justify-content-between">
                    <h5>{ticket.title}</h5>
                    <div className="dropdown">
                        <button className="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown">
                            ⋮
                        </button>
                        <ul className="dropdown-menu">
                            <li>
                                <button className="dropdown-item" onClick={() => setShowConfirm(true)}>
                                    🗑️ Excluir
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                
                {showConfirm && (
                    <div className="alert alert-warning mt-2">
                        <p>Tem certeza que deseja excluir este ticket?</p>
                        <div className="d-flex gap-2">
                            <button 
                                className="btn btn-danger btn-sm"
                                onClick={handleDelete}
                            >
                                Sim, Excluir
                            </button>
                            <button 
                                className="btn btn-secondary btn-sm"
                                onClick={() => setShowConfirm(false)}
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
```

### Vue.js - Método de Deletar
```javascript
// Em um componente Vue
methods: {
    async deleteTicket(ticketId) {
        // Confirmação
        if (!confirm('Tem certeza que deseja excluir este ticket?')) {
            return;
        }

        try {
            const token = localStorage.getItem('token');
            const response = await this.$http.delete(`/api/tickets/${ticketId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            this.$emit('ticket-deleted', ticketId);
            this.$toast.success('Ticket excluído com sucesso!');
            
        } catch (error) {
            console.error('Erro:', error);
            this.$toast.error('Erro ao excluir ticket: ' + error.message);
        }
    }
}
```

## Status dos Tickets
- `aberto`: Ticket recém-criado
- `pendente`: Aguardando ação
- `resolvido`: Problema solucionado
- `finalizado`: Ticket encerrado

## Prioridades
- `baixa`: Prioridade baixa
- `média`: Prioridade média  
- `alta`: Prioridade alta

## Notas Importantes
1. **Sempre confirme antes de deletar** - A exclusão é permanente
2. **Use o token de autenticação** em todas as requisições
3. **Trate erros adequadamente** para melhor experiência do usuário
4. **Recarregue a lista** após deletar um ticket

