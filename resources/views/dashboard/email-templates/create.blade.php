@extends('layout.dashboard.dashboardMain')
@section('title', 'Create Email Template')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Email / Templates /</span> Create Template
        </h4>

        <form method="POST" action="{{ route('email-templates.store') }}" id="email-template-form">
            @include('dashboard.email-templates._form', ['template' => null])
        </form>
    </div>
@endsection