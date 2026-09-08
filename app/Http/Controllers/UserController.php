<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roles', 'tenant');

        if (! auth()->user()->isSuperAdmin()) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        $users = $query
            ->when($request->filled('search'), fn ($query) => $query
                ->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('email', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('tenant_id'), fn ($query) => $query
                ->where('tenant_id', $request->integer('tenant_id')))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $tenants = Tenant::orderBy('company_name')->get();

        return view('dashboard.users.index', compact('users', 'tenants'));
    }

    public function create()
    {
        $roles = $this->assignableRoles();
        $tenants = $this->visibleTenants();

        return view('dashboard.users.create', compact('roles', 'tenants'));
    }

    public function store(Request $request)
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        $role = $request->string('role', 'user')->toString();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($this->assignableRoles())],
        ];

        if ($isSuperAdmin && $role !== 'super-admin') {
            $rules['tenant_id'] = ['required', 'exists:tenants,id'];
        }

        $data = $request->validate($rules);

        $this->assertNoDuplicateAdmin($role, $data['tenant_id'] ?? null, null);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'tenant_id' => $isSuperAdmin
                ? ($data['tenant_id'] ?? null)
                : auth()->user()->tenant_id,
        ]);

        $user->syncRoles([$role]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(int $user)
    {
        $user = $this->findScopedUser($user);
        $roles = $this->assignableRoles();
        $tenants = $this->visibleTenants();

        return view('dashboard.users.edit', compact('user', 'roles', 'tenants'));
    }

    public function update(Request $request, int $user)
    {
        $user = $this->findScopedUser($user);
        $current = auth()->user();

        if ($user->isSuperAdmin() && ! $current->isSuperAdmin()) {
            abort(403);
        }

        $isSelf = $current->id === $user->id;
        $isSuperAdmin = $current->isSuperAdmin();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if (! $isSelf) {
            $rules['role'] = ['required', Rule::in($this->assignableRoles())];
            if ($isSuperAdmin && $request->string('role') !== 'super-admin') {
                $rules['tenant_id'] = ['required', 'exists:tenants,id'];
            }
        }

        $data = $request->validate($rules);

        if (! $isSelf) {
            $role = $data['role'];
            $tenantId = $isSuperAdmin
                ? ($data['tenant_id'] ?? null)
                : $current->tenant_id;

            $this->assertNoDuplicateAdmin($role, $tenantId, $user->id);
        } else {
            $role = $user->getRoleNames()->first() ?? 'user';
            $tenantId = $user->tenant_id;
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => isset($data['password']) && $data['password']
                ? Hash::make($data['password'])
                : $user->password,
            'tenant_id' => $tenantId,
        ]);

        if (! $isSelf) {
            $user->syncRoles([$role]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(int $user)
    {
        $user = $this->findScopedUser($user);
        $current = auth()->user();

        if ($current->id === $user->id) {
            throw ValidationException::withMessages(['email' => 'You cannot delete your own account.']);
        }

        if ($user->isSuperAdmin() && ! $current->isSuperAdmin()) {
            abort(403);
        }

        if (! $current->isSuperAdmin() && $user->isAdmin()) {
            abort(403);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    protected function assignableRoles(): array
    {
        return auth()->user()->isSuperAdmin()
            ? ['user', 'admin', 'super-admin']
            : ['user'];
    }

    protected function visibleTenants()
    {
        return auth()->user()->isSuperAdmin()
            ? Tenant::orderBy('company_name')->get()
            : collect([auth()->user()->tenant])->filter();
    }

    protected function findScopedUser(int $id): User
    {
        $query = User::query()->with('roles', 'tenant');

        if (! auth()->user()->isSuperAdmin()) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        return $query->findOrFail($id);
    }

    protected function assertNoDuplicateAdmin(string $role, ?int $tenantId, ?int $ignoreUserId): void
    {
        if ($role !== 'admin' || ! $tenantId) {
            return;
        }

        $exists = User::where('tenant_id', $tenantId)
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
            ->when($ignoreUserId, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'role' => 'This tenant already has an admin. Only one admin is allowed per tenant.',
            ]);
        }
    }
}
