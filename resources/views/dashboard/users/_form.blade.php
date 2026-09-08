@php
    $user = $user ?? null;
    $isSuperAdmin = Auth::user()->isSuperAdmin();
@endphp

@csrf

<div class="row">
    <div class="mb-3 col-md-6">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $user?->name) }}"
            placeholder="e.g. John Doe"
            autofocus
        />
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            id="email"
            name="email"
            value="{{ old('email', $user?->email) }}"
            placeholder="user@company.com"
        />
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="password" class="form-label">
            Password {{ $user ? '(leave blank to keep current)' : '' }} <span class="text-danger">*</span>
        </label>
        <input
            type="password"
            class="form-control @error('password') is-invalid @enderror"
            id="password"
            name="password"
            placeholder="{{ $user ? '••••••••' : 'Minimum 8 characters' }}"
        />
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input
            type="password"
            class="form-control @error('password_confirmation') is-invalid @enderror"
            id="password_confirmation"
            name="password_confirmation"
            placeholder="••••••••"
        />
    </div>

    <div class="mb-3 col-md-6">
        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $user?->getRoleNames()->first() ?? 'user') === $role)>
                    {{ ucfirst($role) }}
                </option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 col-md-6">
        @if ($isSuperAdmin)
            <label for="tenant_id" class="form-label">
                Tenant <span class="text-danger">*</span>
            </label>
            <select class="form-select @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id">
                <option value="">— No tenant (super-admin) —</option>
                @foreach ($tenants as $tenant)
                    <option value="{{ $tenant->id }}" @selected(old('tenant_id', $user?->tenant_id) == $tenant->id)>
                        {{ $tenant->company_name }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">Required for <code>user</code> and <code>admin</code> roles.</div>
        @else
            <label class="form-label">Tenant</label>
            <input
                type="text"
                class="form-control"
                value="{{ Auth::user()->tenant?->company_name ?? '—' }}"
                disabled
            />
            <div class="form-text">Users are created in your own tenant.</div>
        @endif
        @error('tenant_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-2">
    <button type="submit" class="btn btn-primary me-2">
        {{ $user ? 'Update User' : 'Create User' }}
    </button>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>