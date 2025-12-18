<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filtro de pesquisa
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        // Roles para filtro
        $roles = Role::orderBy('name')->get();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total de Utilizadores',
                'value' => User::count(),
                'color' => 'primary',
                'icon' => 'bi-people'
            ],
            [
                'title' => 'Administradores',
                'value' => User::role('admin')->count(),
                'color' => 'danger',
                'icon' => 'bi-shield-check'
            ],
            
        ];

        return view('admin.v2.users.index', compact('users', 'roles', 'stats'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.v2.users.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.v2.users.index')
            ->with('success', 'Utilizador criado com sucesso!');
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        return view('admin.v2.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user->name = $validated['name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.v2.users.index')
            ->with('success', 'Utilizador atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Não permitir eliminar o próprio utilizador
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.v2.users.index')
                ->with('error', 'Não pode eliminar o seu próprio utilizador!');
        }

        $user->delete();

        return redirect()->route('admin.v2.users.index')
            ->with('success', 'Utilizador eliminado com sucesso!');
    }
}
