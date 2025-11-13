# API de Anexos - Documentação

## Visão Geral

O sistema permite anexar múltiplos arquivos (PDFs, imagens, documentos) aos chamados. Os arquivos são armazenados no storage do Laravel e organizados por ticket.

## Tipos de Arquivos Permitidos

- **Imagens**: jpeg, jpg, png, gif, webp
- **Documentos**: pdf, doc, docx
- **Tamanho máximo**: 10MB por arquivo
- **Quantidade**: Até 10 arquivos por upload

## Rotas da API

### 1. Upload de Arquivos
**POST** `/api/tickets/{ticket}/attachments`

Envia múltiplos arquivos para um ticket.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body (FormData):**
```
arquivos[]: File (múltiplos arquivos)
```

**Exemplo de requisição (Next.js):**
```typescript
const uploadFiles = async (ticketId: number, files: File[]) => {
  const formData = new FormData();
  
  files.forEach((file) => {
    formData.append('arquivos[]', file);
  });

  const response = await fetch(`${API_URL}/tickets/${ticketId}/attachments`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
    body: formData,
  });

  return response.json();
};
```

**Resposta de sucesso (201):**
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

### 2. Listar Anexos de um Ticket
**GET** `/api/tickets/{ticket}/attachments`

Lista todos os anexos de um ticket.

**Headers:**
```
Authorization: Bearer {token}
```

**Exemplo (Next.js):**
```typescript
const getAttachments = async (ticketId: number) => {
  const response = await fetch(`${API_URL}/tickets/${ticketId}/attachments`, {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });

  return response.json();
};
```

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

Visualiza o arquivo no navegador (para imagens e PDFs).

**Headers:**
```
Authorization: Bearer {token}
```

**Exemplo (Next.js):**
```typescript
// Para usar em uma tag <img> ou <iframe>
const imageUrl = `${API_URL}/attachments/${attachmentId}`;
// Adicione o token no header ou use um proxy
```

**Nota:** Para imagens, você pode usar diretamente a propriedade `url` do anexo, que já retorna a URL pública.

### 4. Download de Arquivo
**GET** `/api/attachments/{attachment}/download`

Faz download do arquivo.

**Headers:**
```
Authorization: Bearer {token}
```

**Exemplo (Next.js):**
```typescript
const downloadFile = async (attachmentId: number, fileName: string) => {
  const response = await fetch(`${API_URL}/attachments/${attachmentId}/download`, {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });

  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = fileName;
  document.body.appendChild(a);
  a.click();
  window.URL.revokeObjectURL(url);
  document.body.removeChild(a);
};
```

### 5. Deletar Anexo
**DELETE** `/api/attachments/{attachment}`

Deleta um anexo (apenas admin/support/assistant, clientes não podem deletar).

**Headers:**
```
Authorization: Bearer {token}
```

**Exemplo (Next.js):**
```typescript
const deleteAttachment = async (attachmentId: number) => {
  const response = await fetch(`${API_URL}/attachments/${attachmentId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });

  return response.json();
};
```

**Resposta (200):**
```json
{
  "message": "Anexo deletado com sucesso"
}
```

## Exemplo Completo - Componente React/Next.js

```typescript
'use client';

import { useState } from 'react';

interface Attachment {
  id: number;
  nome_arquivo: string;
  url: string;
  tipo_mime: string;
  tamanho: number;
}

export default function TicketAttachments({ ticketId, token }: { ticketId: number; token: string }) {
  const [files, setFiles] = useState<File[]>([]);
  const [attachments, setAttachments] = useState<Attachment[]>([]);
  const [uploading, setUploading] = useState(false);

  // Carregar anexos ao montar o componente
  const loadAttachments = async () => {
    const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/tickets/${ticketId}/attachments`, {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });
    const data = await response.json();
    setAttachments(data);
  };

  // Upload de arquivos
  const handleUpload = async () => {
    if (files.length === 0) return;

    setUploading(true);
    const formData = new FormData();
    
    files.forEach((file) => {
      formData.append('arquivos[]', file);
    });

    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/tickets/${ticketId}/attachments`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
        body: formData,
      });

      if (response.ok) {
        const data = await response.json();
        setAttachments([...attachments, ...data.anexos]);
        setFiles([]);
        alert('Arquivos enviados com sucesso!');
      }
    } catch (error) {
      console.error('Erro ao enviar arquivos:', error);
    } finally {
      setUploading(false);
    }
  };

  // Deletar anexo
  const handleDelete = async (attachmentId: number) => {
    if (!confirm('Deseja realmente deletar este arquivo?')) return;

    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/attachments/${attachmentId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });

      if (response.ok) {
        setAttachments(attachments.filter(a => a.id !== attachmentId));
        alert('Arquivo deletado com sucesso!');
      }
    } catch (error) {
      console.error('Erro ao deletar arquivo:', error);
    }
  };

  // Formatar tamanho do arquivo
  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };

  // Verificar se é imagem
  const isImage = (mimeType: string) => {
    return mimeType.startsWith('image/');
  };

  return (
    <div className="space-y-4">
      {/* Upload */}
      <div>
        <input
          type="file"
          multiple
          accept="image/*,.pdf,.doc,.docx"
          onChange={(e) => setFiles(Array.from(e.target.files || []))}
          className="mb-2"
        />
        <button
          onClick={handleUpload}
          disabled={files.length === 0 || uploading}
          className="px-4 py-2 bg-blue-500 text-white rounded disabled:opacity-50"
        >
          {uploading ? 'Enviando...' : 'Enviar Arquivos'}
        </button>
      </div>

      {/* Lista de anexos */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {attachments.map((attachment) => (
          <div key={attachment.id} className="border rounded p-4">
            {isImage(attachment.tipo_mime) ? (
              <img
                src={attachment.url}
                alt={attachment.nome_arquivo}
                className="w-full h-48 object-cover rounded mb-2"
              />
            ) : (
              <div className="w-full h-48 bg-gray-200 flex items-center justify-center rounded mb-2">
                <span className="text-gray-500">📄 {attachment.nome_arquivo}</span>
              </div>
            )}
            <p className="text-sm font-medium truncate">{attachment.nome_arquivo}</p>
            <p className="text-xs text-gray-500">{formatFileSize(attachment.tamanho)}</p>
            <div className="mt-2 space-x-2">
              <a
                href={attachment.url}
                target="_blank"
                rel="noopener noreferrer"
                className="text-blue-500 text-sm"
              >
                Ver
              </a>
              <button
                onClick={() => handleDelete(attachment.id)}
                className="text-red-500 text-sm"
              >
                Deletar
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
```

## Permissões

- **Todos os roles** podem fazer upload e visualizar anexos dos tickets que têm permissão de ver
- **Clientes** NÃO podem deletar anexos
- **Admin, Support e Assistant** podem deletar anexos

## Estrutura de Armazenamento

Os arquivos são salvos em:
```
storage/app/public/tickets/{ticket_id}/{uuid}.{extensao}
```

E acessíveis publicamente via:
```
http://seu-dominio.com/storage/tickets/{ticket_id}/{uuid}.{extensao}
```

## Notas Importantes

1. Certifique-se de que o link simbólico do storage foi criado: `php artisan storage:link`
2. O campo `url` no modelo já retorna a URL completa para acesso público
3. Para imagens, você pode usar diretamente a URL no atributo `src` de tags `<img>`
4. Para PDFs, use `<iframe>` ou `<embed>` para visualização no navegador
5. O tamanho máximo é 10MB por arquivo (configurável no controller)

