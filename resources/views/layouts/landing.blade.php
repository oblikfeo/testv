<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @stack('styles')
</head>
<body>
    @include('partials.landing-navbar')

    @yield('content')

    @hasSection('footer')
        @yield('footer')
    @else
        @include('partials.landing-footer')
    @endif
</body>
</html>
