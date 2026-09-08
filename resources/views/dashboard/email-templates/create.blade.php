@extends('layout.dashboard.dashboardMain')
@section('title', 'Create Email Template')
@section('dashboard-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Email / Templates /</span> Create Template
        </h4>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <p class="mb-0">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br />
                    @endforeach
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('email-templates.store') }}" id="email-template-form">
            @include('dashboard.email-templates._form', ['template' => null])
        </form>
    </div>
@endsection