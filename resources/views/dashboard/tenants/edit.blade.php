@extends('layout.dashboard.dashboardMain')
@section('title', 'Edit Tenant')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Management / Tenants /</span> Edit Tenant
        </h4>

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Tenant Admin</h5>
                <a href="{{ route('users.index', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-primary">
                    Manage Users
                </a>
            </div>
            <div class="card-body">
                @if ($tenant->admin)
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($tenant->admin->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <span class="fw-semibold d-block">{{ $tenant->admin->name }}</span>
                            <small class="text-muted">{{ $tenant->admin->email }}</small>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">No admin assigned yet.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">{{ $tenant->company_name }}</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('tenants.update', $tenant) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @include('dashboard.tenants._form', ['tenant' => $tenant, 'plans' => $plans, 'statuses' => $statuses])
                </form>
            </div>
        </div>
    </div>
@endsection