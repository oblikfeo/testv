<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('Вход') }} — {{ config('app.brand_name') }} {{ config('app.brand_suffix') }}</title>
    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    {{ $slot }}
</body>
</html>
