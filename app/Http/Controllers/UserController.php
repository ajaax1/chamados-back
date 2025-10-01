<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // LISTAR USUÁRIOS (apenas admin)
    public function index()
    {
        $this->authorize('manage-users');
        return User::paginate(15);
    }

    // CRIAR USUÁRIO
    public function store(Request $request)
    {
        $this->authorize('manage-users');
        $data = $request->validate([
            'nome' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,suporte,assistente'
        ]);

        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    // ATUALIZAR USUÁRIO
    public function update(Request $request, User $user)
    {
        $this->authorize('manage-users');
        $data = $request->validate([
            'nome' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'role' => 'sometimes|in:admin,suporte,assistente'
        ]);
        $user->update($data);
        return $user;
    }

    // DELETAR USUÁRIO
    public function destroy(User $user)
    {
        $this->authorize('manage-users');
        $user->delete();
        return response()->json(['message' => 'Usuário deletado']);
    }

    // PERFIL DO USUÁRIO LOGADO
    public function me(Request $request)
    {
        return $request->user();
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->update($request->only('nome','email'));
        return $user;
    }
}
