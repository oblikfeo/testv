<!DOCTYPE html>
<html lang="ru" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="format-detection" content="telephone=no">

    {{-- Title & description --}}
    <title>@yield('title', 'AVA VPN — быстрый и приватный VPN для России | Бесплатный тест')</title>
    <meta name="description" content="@yield('meta_description', 'AVA VPN — быстрый и стабильный VPN-сервис для России. Подключение в один клик, защита от блокировок, поддержка всех устройств. Бесплатный тестовый доступ на 8 часов без карты.')">
    <meta name="keywords" content="@yield('meta_keywords', 'VPN, ВПН, купить VPN, VPN сервис, VPN для России, быстрый VPN, безопасный VPN, VPN для Telegram, VPN для Instagram, VPN для компьютера, VPN для телефона, VPN для андроид, VPN для iphone, лучший VPN, VLESS, надёжный VPN')">
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')">
    <meta name="author" content="AVA VPN">

    {{-- Canonical --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="AVA VPN">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', 'AVA VPN — быстрый и приватный VPN для России'))">
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description', 'Быстрый и стабильный VPN-сервис. Подключение в один клик, защита от блокировок, поддержка всех устройств. Тестовый доступ бесплатно.'))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('assets/logo.png'))">
    <meta property="og:image:alt" content="AVA VPN — логотип сервиса">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', View::yieldContent('title', 'AVA VPN — быстрый и приватный VPN для России'))">
    <meta name="twitter:description" content="@yield('og_description', View::yieldContent('meta_description', 'Быстрый и стабильный VPN-сервис. Подключение в один клик. Тестовый доступ бесплатно.'))">
    <meta name="twitter:image" content="@yield('og_image', asset('assets/logo.png'))">

    {{-- Icons --}}
    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo.png') }}">

    {{-- Preconnect for Telegram link --}}
    <link rel="preconnect" href="https://t.me" crossorigin>

    {{-- Stylesheets --}}
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @stack('styles')

    {{-- JSON-LD schemas --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "AVA VPN",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('assets/logo.png') }}",
        "sameAs": [
            "{{ config('app.telegram_support_url') }}"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer support",
            "url": "{{ config('app.telegram_support_url') }}",
            "availableLanguage": ["Russian", "English"]
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "AVA VPN",
        "url": "{{ url('/') }}",
        "inLanguage": "ru-RU"
    }
    </script>
    @stack('jsonld')
</head>
<body>
    @include('partials.landing-navbar')

    <main>
        @yield('content')
    </main>

    @hasSection('footer')
        @yield('footer')
    @else
        @include('partials.landing-footer')
    @endif

    @stack('scripts')
</body>
</html>
