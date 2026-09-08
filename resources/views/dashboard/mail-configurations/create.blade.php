@extends('layout.dashboard.dashboardMain')
@section('title', 'Create Mail Configuration')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Management / Mail Configurations /</span> Create Mail Configuration
        </h4>

        <div class="card">
            <h5 class="card-header">New SMTP Configuration</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('mail-configurations.store') }}">
                    @include('dashboard.mail-configurations._form', ['configuration' => null])
                </form>
            </div>
        </div>
    </div>
@endsection