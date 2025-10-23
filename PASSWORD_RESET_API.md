# Sistema de Recuperação de Senha - API Documentation

## Fluxo Completo de Reset de Senha

### 1. **Solicitar Reset de Senha**
```bash
POST /api/password/forgot
Content-Type: application/json

{
    "email": "usuario@example.com"
}
```

**Resposta de Sucesso:**
```json
{
    "message": "Link de recuperação enviado para seu email.",
    "reset_link": "http://localhost:8000/reset-password?token=abc123&email=usuario@example.com",
    "token": "abc123",
    "expires_in": "1 hora"
}
```

**Resposta de Erro:**
```json
{
    "message": "Este email não está cadastrado em nosso sistema.",
    "errors": {
        "email": ["Este email não está cadastrado em nosso sistema."]
    }
}
```

### 2. **Verificar Token (Opcional)**
```bash
POST /api/password/verify-token
Content-Type: application/json

{
    "token": "abc123",
    "email": "usuario@example.com"
}
```

**Resposta de Sucesso:**
```json
{
    "message": "Token válido",
    "email": "usuario@example.com"
}
```

**Resposta de Erro:**
```json
{
    "message": "Token inválido ou expirado"
}
```

### 3. **Resetar Senha**
```bash
POST /api/password/reset
Content-Type: application/json

{
    "token": "abc123",
    "email": "usuario@example.com",
    "password": "novaSenha123",
    "password_confirmation": "novaSenha123"
}
```

**Resposta de Sucesso:**
```json
{
    "message": "Senha alterada com sucesso!"
}
```

### 4. **Alterar Senha (Usuário Logado)**
```bash
POST /api/password/change
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "senhaAtual123",
    "password": "novaSenha123",
    "password_confirmation": "novaSenha123"
}
```

## Implementação Frontend

### HTML - Página de Esqueci a Senha
```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Recuperar Senha</h3>
                    </div>
                    <div class="card-body">
                        <!-- Step 1: Solicitar Reset -->
                        <div id="step1">
                            <form id="forgotPasswordForm">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar Link de Recuperação</button>
                            </form>
                        </div>

                        <!-- Step 2: Resetar Senha -->
                        <div id="step2" style="display: none;">
                            <form id="resetPasswordForm">
                                <input type="hidden" id="resetToken">
                                <input type="hidden" id="resetEmail">
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" id="password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" id="password_confirmation" required>
                                </div>
                                
                                <button type="submit" class="btn btn-success">Alterar Senha</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Step 1: Solicitar reset
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            
            try {
                const response = await fetch('/api/password/forgot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    alert('Link de recuperação enviado! Verifique seu email.');
                    
                    // Se estiver em desenvolvimento, mostrar o link
                    if (data.reset_link) {
                        const useLink = confirm('Em desenvolvimento. Deseja usar o link direto?');
                        if (useLink) {
                            window.location.href = data.reset_link;
                        }
                    }
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                alert('Erro ao enviar solicitação: ' + error.message);
            }
        });

        // Verificar se há token na URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const email = urlParams.get('email');
        
        if (token && email) {
            // Mostrar formulário de reset
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            document.getElementById('resetToken').value = token;
            document.getElementById('resetEmail').value = email;
        }

        // Step 2: Resetar senha
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const token = document.getElementById('resetToken').value;
            const email = document.getElementById('resetEmail').value;
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;
            
            if (password !== password_confirmation) {
                alert('As senhas não coincidem!');
                return;
            }
            
            try {
                const response = await fetch('/api/password/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token,
                        email,
                        password,
                        password_confirmation
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    alert('Senha alterada com sucesso!');
                    window.location.href = '/login';
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                alert('Erro ao alterar senha: ' + error.message);
            }
        });
    </script>
</body>
</html>
```

### React - Componente de Reset de Senha
```jsx
import React, { useState } from 'react';

function PasswordReset() {
    const [step, setStep] = useState(1);
    const [email, setEmail] = useState('');
    const [token, setToken] = useState('');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [loading, setLoading] = useState(false);

    const handleForgotPassword = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await fetch('/api/password/forgot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Link de recuperação enviado! Verifique seu email.');
                // Em desenvolvimento, pode mostrar o link
                if (data.reset_link) {
                    window.location.href = data.reset_link;
                }
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro ao enviar solicitação: ' + error.message);
        } finally {
            setLoading(false);
        }
    };

    const handleResetPassword = async (e) => {
        e.preventDefault();
        
        if (password !== confirmPassword) {
            alert('As senhas não coincidem!');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch('/api/password/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token,
                    email,
                    password,
                    password_confirmation: confirmPassword
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('Senha alterada com sucesso!');
                window.location.href = '/login';
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro ao alterar senha: ' + error.message);
        } finally {
            setLoading(false);
        }
    };

    // Verificar se há token na URL
    React.useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const urlToken = urlParams.get('token');
        const urlEmail = urlParams.get('email');
        
        if (urlToken && urlEmail) {
            setStep(2);
            setToken(urlToken);
            setEmail(urlEmail);
        }
    }, []);

    return (
        <div className="container mt-5">
            <div className="row justify-content-center">
                <div className="col-md-6">
                    <div className="card">
                        <div className="card-header">
                            <h3>Recuperar Senha</h3>
                        </div>
                        <div className="card-body">
                            {step === 1 ? (
                                <form onSubmit={handleForgotPassword}>
                                    <div className="mb-3">
                                        <label htmlFor="email" className="form-label">Email</label>
                                        <input
                                            type="email"
                                            className="form-control"
                                            id="email"
                                            value={email}
                                            onChange={(e) => setEmail(e.target.value)}
                                            required
                                        />
                                    </div>
                                    <button 
                                        type="submit" 
                                        className="btn btn-primary"
                                        disabled={loading}
                                    >
                                        {loading ? 'Enviando...' : 'Enviar Link de Recuperação'}
                                    </button>
                                </form>
                            ) : (
                                <form onSubmit={handleResetPassword}>
                                    <div className="mb-3">
                                        <label htmlFor="password" className="form-label">Nova Senha</label>
                                        <input
                                            type="password"
                                            className="form-control"
                                            id="password"
                                            value={password}
                                            onChange={(e) => setPassword(e.target.value)}
                                            required
                                        />
                                    </div>
                                    <div className="mb-3">
                                        <label htmlFor="confirmPassword" className="form-label">
                                            Confirmar Nova Senha
                                        </label>
                                        <input
                                            type="password"
                                            className="form-control"
                                            id="confirmPassword"
                                            value={confirmPassword}
                                            onChange={(e) => setConfirmPassword(e.target.value)}
                                            required
                                        />
                                    </div>
                                    <button 
                                        type="submit" 
                                        className="btn btn-success"
                                        disabled={loading}
                                    >
                                        {loading ? 'Alterando...' : 'Alterar Senha'}
                                    </button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default PasswordReset;
```

## Configuração de Email (Produção)

### .env
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu-email@gmail.com
MAIL_FROM_NAME="Sistema de Chamados"
```

### Implementar Email Real
```php
// No PasswordResetController::forgotPassword()
use Illuminate\Support\Facades\Mail;

// Substituir o retorno por:
Mail::send('emails.password-reset', [
    'resetLink' => $resetLink,
    'user' => $user
], function ($message) use ($user) {
    $message->to($user->email)
            ->subject('Recuperação de Senha');
});
```

## Segurança Implementada

1. **Tokens únicos** - Str::random(64)
2. **Expiração** - 1 hora
3. **Uso único** - Token deletado após uso
4. **Validação** - Email deve existir
5. **Confirmação** - Senha deve ser confirmada
6. **Hash seguro** - Hash::make() para senhas

## Códigos de Resposta

| Código | Significado |
|--------|-------------|
| 200 | Sucesso |
| 400 | Token inválido/expirado |
| 404 | Email/usuário não encontrado |
| 422 | Dados inválidos |
