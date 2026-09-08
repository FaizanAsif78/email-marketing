@extends('layout.dashboard.dashboardMain')
@section('title', 'Create Tenant')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Management / Tenants /</span> Create Tenant
        </h4>

        <div class="card">
            <h5 class="card-header">Company Details</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('tenants.store') }}" enctype="multipart/form-data">
                    @include('dashboard.tenants._form', ['tenant' => null, 'plans' => $plans, 'statuses' => $statuses])
                </form>
            </div>
        </div>
    </div>
@endsection