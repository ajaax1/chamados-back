# API de Clientes - Guia para Frontend

## 📋 Visão Geral

Esta rota retorna todos os clientes cadastrados no sistema, ideal para popular selects, dropdowns e listas de seleção no frontend.

## 🔗 Endpoint

**GET** `/api/clientes`

**Autenticação:** Requer token Sanctum (usuário deve estar logado)

**Headers:**
```
Authorization: Bearer {seu_token_aqui}
Content-Type: application/json
```

## 📤 Resposta

### Sucesso (200)

Retorna um array de objetos com os clientes ordenados alfabeticamente por nome:

```json
[
  {
    "id": 1,
    "name": "Ana Silva",
    "email": "ana@exemplo.com"
  },
  {
    "id": 2,
    "name": "João Santos",
    "email": "joao@exemplo.com"
  },
  {
    "id": 3,
    "name": "Maria Oliveira",
    "email": "maria@exemplo.com"
  }
]
```

### Erro (401 - Não autenticado)

```json
{
  "message": "Unauthenticated."
}
```

## 💻 Exemplos de Implementação

### 1. JavaScript Vanilla / Fetch API

```javascript
/**
 * Busca todos os clientes do sistema
 * @param {string} token - Token de autenticação
 * @returns {Promise<Array>} Array de clientes
 */
async function buscarClientes(token) {
  try {
    const response = await fetch('/api/clientes', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) {
      if (response.status === 401) {
        throw new Error('Não autenticado. Faça login novamente.');
      }
      throw new Error('Erro ao buscar clientes');
    }

    const clientes = await response.json();
    return clientes;
  } catch (error) {
    console.error('Erro ao buscar clientes:', error);
    throw error;
  }
}

// Uso
const token = localStorage.getItem('auth_token');
const clientes = await buscarClientes(token);
console.log('Clientes:', clientes);
```

### 2. React com Hooks

```jsx
import { useState, useEffect } from 'react';

function ClienteSelect({ onSelectCliente, selectedClienteId }) {
  const [clientes, setClientes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchClientes() {
      try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('/api/clientes', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Erro ao buscar clientes');
        }

        const data = await response.json();
        setClientes(data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }

    fetchClientes();
  }, []);

  if (loading) return <div>Carregando clientes...</div>;
  if (error) return <div>Erro: {error}</div>;

  return (
    <select 
      value={selectedClienteId || ''} 
      onChange={(e) => onSelectCliente(e.target.value)}
      className="form-select"
    >
      <option value="">Selecione um cliente</option>
      {clientes.map(cliente => (
        <option key={cliente.id} value={cliente.id}>
          {cliente.name} ({cliente.email})
        </option>
      ))}
    </select>
  );
}

// Uso do componente
function MeuFormulario() {
  const [clienteId, setClienteId] = useState('');

  return (
    <form>
      <label>Cliente:</label>
      <ClienteSelect 
        selectedClienteId={clienteId}
        onSelectCliente={setClienteId}
      />
    </form>
  );
}
```

### 3. React com Custom Hook

```jsx
import { useState, useEffect } from 'react';

/**
 * Custom hook para buscar clientes
 */
function useClientes() {
  const [clientes, setClientes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchClientes() {
      try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch('/api/clientes', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Erro ao buscar clientes');
        }

        const data = await response.json();
        setClientes(data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }

    fetchClientes();
  }, []);

  return { clientes, loading, error };
}

// Uso do hook
function ClienteSelect() {
  const { clientes, loading, error } = useClientes();

  if (loading) return <div>Carregando...</div>;
  if (error) return <div>Erro: {error}</div>;

  return (
    <select>
      <option value="">Selecione um cliente</option>
      {clientes.map(cliente => (
        <option key={cliente.id} value={cliente.id}>
          {cliente.name}
        </option>
      ))}
    </select>
  );
}
```

### 4. Vue.js 3 (Composition API)

```vue
<template>
  <select v-model="selectedClienteId" @change="onClienteChange">
    <option value="">Selecione um cliente</option>
    <option 
      v-for="cliente in clientes" 
      :key="cliente.id" 
      :value="cliente.id"
    >
      {{ cliente.name }} ({{ cliente.email }})
    </option>
  </select>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const clientes = ref([]);
const selectedClienteId = ref('');
const loading = ref(true);
const error = ref(null);

async function fetchClientes() {
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/clientes', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) {
      throw new Error('Erro ao buscar clientes');
    }

    const data = await response.json();
    clientes.value = data;
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

function onClienteChange() {
  console.log('Cliente selecionado:', selectedClienteId.value);
}

onMounted(() => {
  fetchClientes();
});
</script>
```

### 5. Axios (com interceptor)

```javascript
import axios from 'axios';

// Configurar interceptor para adicionar token automaticamente
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

/**
 * Busca todos os clientes
 */
async function buscarClientes() {
  try {
    const response = await axios.get('/api/clientes');
    return response.data;
  } catch (error) {
    if (error.response?.status === 401) {
      // Redirecionar para login
      window.location.href = '/login';
    }
    throw error;
  }
}

// Uso
const clientes = await buscarClientes();
```

### 6. Service/API Layer Pattern

```javascript
// services/clienteService.js
class ClienteService {
  constructor(apiClient) {
    this.api = apiClient;
  }

  async getAll() {
    try {
      const response = await this.api.get('/clientes');
      return response.data;
    } catch (error) {
      console.error('Erro ao buscar clientes:', error);
      throw error;
    }
  }

  // Método auxiliar para formatar para select
  formatForSelect(clientes) {
    return clientes.map(cliente => ({
      value: cliente.id,
      label: `${cliente.name} (${cliente.email})`
    }));
  }
}

// Uso
const clienteService = new ClienteService(apiClient);
const clientes = await clienteService.getAll();
const options = clienteService.formatForSelect(clientes);
```

### 7. Com tratamento de erro completo

```javascript
async function buscarClientesComTratamento(token) {
  try {
    const response = await fetch('/api/clientes', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    // Verificar status da resposta
    if (response.status === 401) {
      // Token expirado ou inválido
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
      return;
    }

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || 'Erro ao buscar clientes');
    }

    const clientes = await response.json();
    return {
      success: true,
      data: clientes
    };
  } catch (error) {
    console.error('Erro ao buscar clientes:', error);
    return {
      success: false,
      error: error.message
    };
  }
}

// Uso
const resultado = await buscarClientesComTratamento(token);
if (resultado.success) {
  console.log('Clientes:', resultado.data);
} else {
  alert('Erro: ' + resultado.error);
}
```

## 🎨 Exemplo Completo: Select com Busca

```jsx
import { useState, useEffect, useMemo } from 'react';

function ClienteSelectComBusca({ onSelectCliente }) {
  const [clientes, setClientes] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchClientes() {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/clientes', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });
      const data = await response.json();
      setClientes(data);
      setLoading(false);
    }
    fetchClientes();
  }, []);

  // Filtrar clientes baseado no termo de busca
  const filteredClientes = useMemo(() => {
    if (!searchTerm) return clientes;
    return clientes.filter(cliente =>
      cliente.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      cliente.email.toLowerCase().includes(searchTerm.toLowerCase())
    );
  }, [clientes, searchTerm]);

  if (loading) return <div>Carregando...</div>;

  return (
    <div>
      <input
        type="text"
        placeholder="Buscar cliente..."
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        className="form-input"
      />
      <select 
        onChange={(e) => onSelectCliente(e.target.value)}
        className="form-select"
      >
        <option value="">Selecione um cliente</option>
        {filteredClientes.map(cliente => (
          <option key={cliente.id} value={cliente.id}>
            {cliente.name} ({cliente.email})
          </option>
        ))}
      </select>
    </div>
  );
}
```

## 📝 Notas Importantes

1. **Autenticação obrigatória:** A rota requer autenticação. Sempre inclua o token no header.

2. **Ordenação:** Os clientes já vêm ordenados alfabeticamente por nome (A-Z).

3. **Campos retornados:** Apenas `id`, `name` e `email` são retornados para otimizar a resposta.

4. **Cache:** Considere implementar cache no frontend se os clientes não mudam frequentemente.

5. **Tratamento de erros:** Sempre trate erros de autenticação (401) e outros erros de rede.

6. **Loading states:** Mostre um estado de carregamento enquanto busca os dados.

## 🔄 Atualização em Tempo Real (Opcional)

Se precisar atualizar a lista de clientes em tempo real:

```javascript
// Com polling (buscar a cada X segundos)
useEffect(() => {
  const interval = setInterval(() => {
    buscarClientes();
  }, 30000); // Atualizar a cada 30 segundos

  return () => clearInterval(interval);
}, []);
```

## ✅ Checklist de Implementação

- [ ] Adicionar tratamento de erro para 401 (não autenticado)
- [ ] Mostrar estado de carregamento
- [ ] Implementar tratamento de erros de rede
- [ ] Validar se o token existe antes de fazer a requisição
- [ ] Considerar cache para melhor performance
- [ ] Adicionar opção "Selecione um cliente" no select
- [ ] Formatar exibição (nome + email)

---

**Última atualização:** 2025-11-17

