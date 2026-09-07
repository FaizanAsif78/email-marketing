@extends('main', ['htmlClass' => 'light-style'])
@push('styles')
    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}" />
@endpush
@section('main-content')
    @yield('misc-content')
@endsection