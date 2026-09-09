@extends('layout.dashboard.dashboardMain')
@section('title', 'My Profile')
@section('dashboard-content')
    @php
        $user = auth()->user();
        $initials = Str::of((string) $user->name)
            ->split('/[\s]+/')
            ->filter()
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Account Settings /</span> Profile</h4>

        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills flex-column flex-md-row mb-3">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('account-settings.account') }}">
                            <i class="bx bx-user me-1"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('account-settings.notifications') }}">
                            <i class="bx bx-bell me-1"></i> Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('account-settings.connections') }}">
                            <i class="bx bx-link-alt me-1"></i> Connections
                        </a>
                    </li>
                </ul>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <h5 class="card-header">Profile Details</h5>
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-4 mb-4">
                                    <span class="avatar avatar-lg">
                                        <span class="avatar-initial bg-label-primary rounded-circle fs-2">
                                            {{ $initials }}
                                        </span>
                                    </span>
                                    <div>
                                        <h5 class="mb-0">{{ $user->name }}</h5>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('account-settings.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                class="form-control @error('name') is-invalid @enderror"
                                                id="name"
                                                name="name"
                                                value="{{ old('name', $user->name) }}"
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
                                                value="{{ old('email', $user->email) }}"
                                                placeholder="user@company.com"
                                            />
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="password" class="form-label">
                                                New Password <span class="text-muted fw-normal">(leave blank to keep current)</span>
                                            </label>
                                            <input
                                                type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password"
                                                name="password"
                                                placeholder="Minimum 8 characters"
                                            />
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                            <input
                                                type="password"
                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                id="password_confirmation"
                                                name="password_confirmation"
                                                placeholder="••••••••"
                                            />
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-save me-1"></i> Save changes
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <h5 class="card-header">Account Details</h5>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Role</label>
                                    <div>
                                        @foreach ($user->getRoleNames() as $role)
                                            <span class="badge bg-label-primary me-1">{{ ucfirst($role) }}</span>
                                        @endforeach
                                        @if ($user->getRoleNames()->isEmpty())
                                            <span class="badge bg-label-secondary">User</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tenant / Company</label>
                                    <p class="mb-0 fw-semibold">{{ $user->tenant?->company_name ?? '—' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Email</label>
                                    <p class="mb-0 fw-semibold text-break">{{ $user->email }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Member since</label>
                                    <p class="mb-0 fw-semibold">{{ $user->created_at?->format('M j, Y') ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="form-label text-muted">Last updated</label>
                                    <p class="mb-0 fw-semibold">{{ $user->updated_at?->format('M j, Y') ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection