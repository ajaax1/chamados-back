<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // LISTAR USUÁRIOS (todos podem ver)
    public function index(Request $request)
    {
        $query = User::query();

        // Filtro por nome
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filtro por role
        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        // Ordenação
        $query->orderBy('name', 'desc');

        return response()->json($query->paginate(15));
    }

    // CRIAR USUÁRIO
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'role' => 'required|in:admin,support,assistant'
            ],
            [
                'name.required' => 'O nome é obrigatório.',
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'O email deve ser um endereço de email válido.',
                'email.unique' => 'O email já está em uso.',
                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
                'role.required' => 'A função é obrigatória.',
                'role.in' => 'A função deve ser um dos seguintes: admin, support, assistant.',
            ]
        );

        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    // ATUALIZAR USUÁRIO
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Verificar se o usuário pode editar este usuário
        if (!$currentUser->canManageUsers() && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Acesso negado. Você só pode editar seus próprios dados.'], 403);
        }

        // Se não é admin, não pode alterar role
        $validationRules = [
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ];

        // Apenas admin pode alterar role
        if ($currentUser->canManageUsers()) {
            $validationRules['role'] = 'sometimes|in:admin,support,assistant';
        }

        $data = $request->validate(
            $validationRules,
            [
                'name.max' => 'O nome não pode ter mais de 100 caracteres.',
                'email.email' => 'O email deve ser um endereço de email válido.',
                'email.unique' => 'O email já está em uso.',
                'role.in' => 'A função deve ser um dos seguintes: admin, support, assistant.',
            ]
        );

        $user->update($data);
        return $user;
    }

    // VISUALIZAR USUÁRIO (todos podem ver)
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
        return $user;
    }

    // DELETAR USUÁRIO
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }   
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
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ], [
            'name.max' => 'O nome não pode ter mais de 100 caracteres.',
            'email.email' => 'O email deve ser um endereço de email válido.',
            'email.unique' => 'O email já está em uso.',
        ]);

        $user->update($data);
        return $user;
    }

    public function list()
    {
        $users = User::select('id', 'name')->get();
        return response()->json($users);
    }

    // BUSCAR TODOS OS USUÁRIOS EM ORDEM ALFABÉTICA
    public function getAllAlphabetical()
    {
        $users = User::orderBy('name', 'asc')->get();
        return response()->json($users);
    }
}
