<nav class="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-logo">
            <img src="{{ asset('assets/logo.png') }}" alt="AVA VPN">
            <span>AVA <em>VPN</em></span>
        </a>
        <div class="navbar-actions">
            @auth
                <a href="{{ route('cabinet.subscription') }}" class="btn btn-primary btn-sm">Кабинет</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Войти</a>
            @endauth
        </div>
    </div>
</nav>
