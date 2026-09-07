@extends('main', ['htmlClass' => 'light-style customizer-hide'])
@push('styles')
    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
@endpush
@section('main-content')
    @yield('auth-content')
@endsection