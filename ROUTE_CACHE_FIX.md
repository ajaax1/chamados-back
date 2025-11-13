# 🔧 Correção: Rota de Anexos Não Encontrada

## Problema
A rota `api/tickets/{ticket}/attachments` retorna erro 404 (NotFoundHttpException).

## Solução

A rota está corretamente definida no código, mas o servidor pode ter cache de rotas desatualizado.

### No Servidor (SSH)

Execute os seguintes comandos no servidor:

```bash
# Limpar cache de rotas
php artisan route:clear

# Limpar cache de configuração
php artisan config:clear

# Limpar cache de aplicação
php artisan cache:clear

# Recriar cache de rotas (opcional, para produção)
php artisan route:cache
```

### Verificar Rotas

Para verificar se a rota está registrada:

```bash
php artisan route:list --path=tickets
```

Você deve ver:
```
GET|HEAD  api/tickets/{ticket}/attachments ...... AttachmentController@index
POST      api/tickets/{ticket}/attachments ...... AttachmentController@store
```

## Rotas de Anexos Disponíveis

- `GET /api/tickets/{ticket}/attachments` - Listar anexos
- `POST /api/tickets/{ticket}/attachments` - Upload de anexos
- `GET /api/attachments/{attachment}` - Visualizar anexo
- `GET /api/attachments/{attachment}/download` - Download anexo
- `DELETE /api/attachments/{attachment}` - Deletar anexo

## Autenticação

Todas as rotas de anexos requerem autenticação:
```
Authorization: Bearer {token}
```

## Exemplo de Uso

```javascript
// Listar anexos de um ticket
fetch('https://seu-dominio.com/api/tickets/21/attachments', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

