<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
            'phone' => 'nullable|string|max:30',
            'location' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:40',
            'role' => 'required|exists:roles,name',
            'referral_code' => 'nullable|string|max:100|alpha_dash|unique:users,referral_code',
            'commission_fixed_value' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'location' => $validated['location'] ?? null,
            'nif' => $validated['nif'] ?? null,
            'iban' => $validated['iban'] ?? null,
            // Password aleatória e desconhecida — o utilizador define a sua própria
            // através do link enviado por email (ver sendSetPasswordEmail abaixo).
            'password' => Str::random(40),
            'referral_code' => $this->resolveReferralCode($validated),
            'commission_fixed_value' => $this->resolveCommissionValue($validated),
        ]);

        $user->assignRole($validated['role']);

        $this->sendSetPasswordEmail($user);

        return redirect()->route('admin.v2.users.index')
            ->with('success', 'Utilizador criado com sucesso! Foi enviado um email para definir a password.');
    }

    /**
     * Gera um token de "primeiro acesso" (broker próprio, com validade mais
     * longa que o "esqueci-me da password") e envia o email para o definir.
     */
    private function sendSetPasswordEmail(User $user): void
    {
        $token = Password::broker('setup')->createToken($user);
        $url = route('password.setup', ['token' => $token, 'email' => $user->email]);

        Mail::to($user->email)->send(new SetPasswordMail($user, $url));
    }

    /**
     * Se o perfil for angariador e não for indicado um código, é gerado
     * automaticamente a partir do nome — todo o angariador precisa de um
     * código para o seu link pessoal funcionar.
     */
    private function resolveReferralCode(array $validated): ?string
    {
        if (!empty($validated['referral_code'])) {
            return $validated['referral_code'];
        }

        if ($validated['role'] !== 'angariador') {
            return null;
        }

        return User::generateUniqueReferralCode($validated['name'], $validated['last_name']);
    }

    /**
     * Se o perfil for angariador e não for indicado um valor de comissão,
     * assume-se o valor por defeito de 100€.
     */
    private function resolveCommissionValue(array $validated): ?float
    {
        if ($validated['role'] !== 'angariador') {
            return $validated['commission_fixed_value'] ?? null;
        }

        return $validated['commission_fixed_value'] ?? 100;
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
            'phone' => 'nullable|string|max:30',
            'location' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:40',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
            'referral_code' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'referral_code')->ignore($user->id)],
            'commission_fixed_value' => 'nullable|numeric|min:0',
        ]);

        $user->name = $validated['name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->location = $validated['location'] ?? null;
        $user->nif = $validated['nif'] ?? null;
        $user->iban = $validated['iban'] ?? null;
        $user->referral_code = $validated['referral_code'] ?? null;
        $user->commission_fixed_value = $validated['commission_fixed_value'] ?? null;

        if ($request->filled('password')) {
            $user->password = $validated['password'];
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
