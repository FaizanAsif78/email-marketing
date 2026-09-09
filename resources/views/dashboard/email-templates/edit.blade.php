@extends('layout.dashboard.dashboardMain')
@section('title', 'Edit Email Template')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Email / Templates /</span> Edit Template
        </h4>

        <form method="POST" action="{{ route('email-templates.update', $template) }}" id="email-template-form">
            @method('PUT')
            @include('dashboard.email-templates._form', ['template' => $template])
        </form>
    </div>
@endsection