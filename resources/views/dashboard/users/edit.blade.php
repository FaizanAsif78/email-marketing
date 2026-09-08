@extends('layout.dashboard.dashboardMain')
@section('title', 'Edit User')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Management / Users /</span> Edit User
        </h4>

        <div class="card">
            <h5 class="card-header">{{ $user->name }}</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @method('PUT')
                    @include('dashboard.users._form', ['user' => $user, 'roles' => $roles, 'tenants' => $tenants])
                </form>
            </div>
        </div>
    </div>
@endsection