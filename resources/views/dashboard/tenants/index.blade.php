@extends('layout.dashboard.dashboardMain')
@section('title', 'Tenants')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Management /</span> Tenants</h4>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header flex-column flex-md-row">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <h5 class="card-title mb-0">Companies</h5>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('tenants.index') }}" class="d-flex">
                            <input
                                type="text"
                                class="form-control me-2"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search by company name"
                            />
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i>
                            </button>
                        </form>
                        <a href="{{ route('tenants.create') }}" class="btn btn-primary text-nowrap">
                            <i class="bx bx-plus me-1"></i> Add Tenant
                        </a>
                    </div>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Admin</th>
                            <th>Website</th>
                            <th>Timezone</th>
                            <th>Country</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($tenants as $tenant)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            @if ($tenant->company_logo)
                                                <img src="{{ Storage::url($tenant->company_logo) }}" alt="{{ $tenant->company_name }}" class="rounded-circle" />
                                            @else
                                                <span class="avatar-initial bg-label-primary rounded-circle">
                                                    {{ strtoupper(substr($tenant->company_name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="fw-semibold">{{ $tenant->company_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($tenant->admin)
                                        <span class="fw-semibold d-block">{{ $tenant->admin->name }}</span>
                                        <small class="text-muted">{{ $tenant->admin->email }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($tenant->company_website)
                                        <a href="{{ $tenant->company_website }}" target="_blank" rel="noopener">
                                            {{ str_replace(['https://', 'http://', 'www.'], '', $tenant->company_website) }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $tenant->timezone }}</td>
                                <td>{{ $tenant->country }}</td>
                                <td>
                                    <span class="badge bg-label-info">{{ ucfirst($tenant->subscription_plan) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColor = match ($tenant->status) {
                                            'active' => 'success',
                                            'inactive' => 'secondary',
                                            'suspended' => 'danger',
                                            'pending' => 'warning',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColor }}">{{ ucfirst($tenant->status) }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('tenants.edit', $tenant) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                            <form method="POST" action="{{ route('tenants.destroy', $tenant) }}" onsubmit="return confirm('Are you sure you want to delete this tenant?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No tenants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $tenants->links() }}
            </div>
        </div>
    </div>
@endsection