<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $htmlClass ?? 'light-style layout-menu-fixed' }}" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastify/toastify.min.css') }}" />

    @stack('styles')

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    @yield('main-content')

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Toastify notifications (project-wide) -->
    <script src="{{ asset('assets/vendor/libs/toastify/toastify.min.js') }}"></script>
    @php
        $__appMessages = [];
        foreach (['success', 'info', 'warning', 'error'] as $__flashKey) {
            if (session($__flashKey)) {
                $__appMessages[$__flashKey] = session($__flashKey);
            }
        }
        if (isset($errors) && $errors->any()) {
            $__appMessages['danger'] = collect($errors->all())->implode(' ');
        }
    @endphp
    <script>
        window.AppMessages = {!! Js::from($__appMessages) !!};

        window.showToast = function (message, type, options) {
            var styles = {
                success: { background: '#2e7d32', color: '#ffffff' },
                danger: { background: '#d32f2f', color: '#ffffff' },
                warning: { background: '#f59f00', color: '#312e0a' },
                info: { background: '#0d6efd', color: '#ffffff' },
            };
            var style = styles[type] || styles.info;
            if (options && options.style) {
                style = Object.assign({}, style, options.style);
            }
            return Toastify({
                text: message,
                duration: options && typeof options.duration === 'number' ? options.duration : 4500,
                gravity: 'bottom',
                position: 'right',
                close: true,
                stopOnFocus: true,
                style: style,
                onClick: options && options.onClick ? options.onClick : undefined,
            }).showToast();
        };

        window.addEventListener('DOMContentLoaded', function () {
            if (!window.AppMessages) return;
            Object.keys(window.AppMessages).forEach(function (key) {
                window.showToast(window.AppMessages[key], key === 'error' ? 'danger' : key);
            });
        });
    </script>

    @stack('scripts')
</body>

</html>