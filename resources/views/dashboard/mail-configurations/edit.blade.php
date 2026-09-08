@extends('layout.dashboard.dashboardMain')
@section('title', 'Edit Mail Configuration')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Management / Mail Configurations /</span> Edit Mail Configuration
        </h4>

        <div class="card">
            <h5 class="card-header">{{ $configuration->smtp_host }}</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('mail-configurations.update', $configuration) }}">
                    @method('PUT')
                    @include('dashboard.mail-configurations._form', ['configuration' => $configuration])
                </form>
            </div>
        </div>
    </div>
@endsection