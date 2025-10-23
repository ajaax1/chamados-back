# Frontend - Página de Reset de Senha

## Configuração do Backend

### 1. **Variável de Ambiente**
Adicione ao seu `.env`:
```env
FRONTEND_URL=http://localhost:3000
```

### 2. **Rotas da API**
```bash
# Verificar token (GET)
GET /api/password/verify-token?token=abc123&email=user@example.com

# Resetar senha (POST)
POST /api/password/reset
{
    "token": "abc123",
    "email": "user@example.com", 
    "password": "nova_senha123",
    "password_confirmation": "nova_senha123"
}
```

## Exemplo React - ResetPassword.jsx

```jsx
import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';

const ResetPassword = () => {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    
    const [formData, setFormData] = useState({
        password: '',
        password_confirmation: ''
    });
    const [loading, setLoading] = useState(false);
    const [tokenValid, setTokenValid] = useState(null);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const token = searchParams.get('token');
    const email = searchParams.get('email');

    useEffect(() => {
        // Verificar se o token é válido ao carregar a página
        if (token && email) {
            verifyToken();
        } else {
            setError('Token ou email não encontrado na URL');
        }
    }, [token, email]);

    const verifyToken = async () => {
        try {
            const response = await fetch(
                `/api/password/verify-token?token=${token}&email=${email}`
            );
            
            if (response.ok) {
                setTokenValid(true);
            } else {
                const data = await response.json();
                setError(data.message);
            }
        } catch (error) {
            setError('Erro ao verificar token');
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            const response = await fetch('/api/password/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token,
                    email,
                    password: formData.password,
                    password_confirmation: formData.password_confirmation
                })
            });

            const data = await response.json();

            if (response.ok) {
                setSuccess(true);
                setTimeout(() => {
                    navigate('/login');
                }, 3000);
            } else {
                setError(data.message);
            }
        } catch (error) {
            setError('Erro ao alterar senha');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    if (tokenValid === null) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-500 mx-auto"></div>
                    <p className="mt-4 text-gray-600">Verificando token...</p>
                </div>
            </div>
        );
    }

    if (!tokenValid) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
                    <div className="text-center">
                        <div className="text-red-500 text-6xl mb-4">❌</div>
                        <h1 className="text-2xl font-bold text-gray-900 mb-4">
                            Token Inválido
                        </h1>
                        <p className="text-gray-600 mb-6">{error}</p>
                        <button
                            onClick={() => navigate('/forgot-password')}
                            className="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600"
                        >
                            Solicitar Novo Link
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    if (success) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
                    <div className="text-center">
                        <div className="text-green-500 text-6xl mb-4">✅</div>
                        <h1 className="text-2xl font-bold text-gray-900 mb-4">
                            Senha Alterada!
                        </h1>
                        <p className="text-gray-600 mb-6">
                            Sua senha foi alterada com sucesso. Você será redirecionado para o login.
                        </p>
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="flex items-center justify-center min-h-screen bg-gray-50">
            <div className="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold text-gray-900">
                        Redefinir Senha
                    </h1>
                    <p className="text-gray-600 mt-2">
                        Digite sua nova senha para {email}
                    </p>
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                            Nova Senha
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            value={formData.password}
                            onChange={handleChange}
                            required
                            minLength={6}
                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Digite sua nova senha"
                        />
                    </div>

                    <div>
                        <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                            Confirmar Nova Senha
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            value={formData.password_confirmation}
                            onChange={handleChange}
                            required
                            minLength={6}
                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Confirme sua nova senha"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                        {loading ? 'Alterando...' : 'Alterar Senha'}
                    </button>
                </form>

                <div className="mt-6 text-center">
                    <p className="text-sm text-gray-600">
                        Lembrou da senha?{' '}
                        <button
                            onClick={() => navigate('/login')}
                            className="text-blue-600 hover:text-blue-500"
                        >
                            Fazer Login
                        </button>
                    </p>
                </div>
            </div>
        </div>
    );
};

export default ResetPassword;
```

## Exemplo Vue.js - ResetPassword.vue

```vue
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
      <!-- Loading State -->
      <div v-if="loading && !tokenChecked" class="text-center">
        <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-4 text-gray-600">Verificando token...</p>
      </div>

      <!-- Invalid Token -->
      <div v-else-if="!tokenValid" class="text-center">
        <div class="text-red-500 text-6xl mb-4">❌</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Token Inválido</h1>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <button
          @click="$router.push('/forgot-password')"
          class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600"
        >
          Solicitar Novo Link
        </button>
      </div>

      <!-- Success State -->
      <div v-else-if="success" class="text-center">
        <div class="text-green-500 text-6xl mb-4">✅</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Senha Alterada!</h1>
        <p class="text-gray-600 mb-6">
          Sua senha foi alterada com sucesso. Você será redirecionado para o login.
        </p>
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
      </div>

      <!-- Reset Form -->
      <div v-else>
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold text-gray-900">Redefinir Senha</h1>
          <p class="text-gray-600 mt-2">Digite sua nova senha para {{ email }}</p>
        </div>

        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {{ error }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Nova Senha
            </label>
            <input
              type="password"
              id="password"
              v-model="formData.password"
              required
              minlength="6"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Digite sua nova senha"
            />
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirmar Nova Senha
            </label>
            <input
              type="password"
              id="password_confirmation"
              v-model="formData.password_confirmation"
              required
              minlength="6"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="Confirme sua nova senha"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ loading ? 'Alterando...' : 'Alterar Senha' }}
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Lembrou da senha?
            <button
              @click="$router.push('/login')"
              class="text-blue-600 hover:text-blue-500"
            >
              Fazer Login
            </button>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ResetPassword',
  data() {
    return {
      formData: {
        password: '',
        password_confirmation: ''
      },
      loading: false,
      tokenValid: null,
      error: '',
      success: false,
      token: null,
      email: null
    }
  },
  async mounted() {
    this.token = this.$route.query.token;
    this.email = this.$route.query.email;
    
    if (this.token && this.email) {
      await this.verifyToken();
    } else {
      this.error = 'Token ou email não encontrado na URL';
    }
  },
  methods: {
    async verifyToken() {
      this.loading = true;
      try {
        const response = await fetch(
          `/api/password/verify-token?token=${this.token}&email=${this.email}`
        );
        
        if (response.ok) {
          this.tokenValid = true;
        } else {
          const data = await response.json();
          this.error = data.message;
        }
      } catch (error) {
        this.error = 'Erro ao verificar token';
      } finally {
        this.loading = false;
      }
    },

    async handleSubmit() {
      this.loading = true;
      this.error = '';

      try {
        const response = await fetch('/api/password/reset', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            token: this.token,
            email: this.email,
            password: this.formData.password,
            password_confirmation: this.formData.password_confirmation
          })
        });

        const data = await response.json();

        if (response.ok) {
          this.success = true;
          setTimeout(() => {
            this.$router.push('/login');
          }, 3000);
        } else {
          this.error = data.message;
        }
      } catch (error) {
        this.error = 'Erro ao alterar senha';
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
```

## Configuração das Rotas

### React Router
```jsx
import { Routes, Route } from 'react-router-dom';
import ResetPassword from './components/ResetPassword';

function App() {
  return (
    <Routes>
      <Route path="/reset-password" element={<ResetPassword />} />
    </Routes>
  );
}
```

### Vue Router
```js
import ResetPassword from '@/views/ResetPassword.vue';

const routes = [
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword
  }
];
```

## Testando o Fluxo

### 1. **Solicitar Reset**
```bash
curl -X POST http://localhost:8000/api/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com"}'
```

### 2. **Verificar Token**
```bash
curl "http://localhost:8000/api/password/verify-token?token=abc123&email=admin@example.com"
```

### 3. **Resetar Senha**
```bash
curl -X POST http://localhost:8000/api/password/reset \
  -H "Content-Type: application/json" \
  -d '{
    "token": "abc123",
    "email": "admin@example.com",
    "password": "nova_senha123",
    "password_confirmation": "nova_senha123"
  }'
```

## Configuração do .env

```env
# URL do seu frontend
FRONTEND_URL=http://localhost:3000

# Configurações de email
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=465
MAIL_USERNAME=site@revistaimagemindustrial.com
MAIL_PASSWORD=J~rN]4g%:8W-,)}
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=site@revistaimagemindustrial.com
MAIL_FROM_NAME="Revista-Imagem-Industrial"
```

Agora o sistema está configurado para redirecionar para o seu frontend, onde o usuário poderá inserir a nova senha de forma segura!
