@php
    $active = $activeRoute ?? '';
@endphp
<aside class="cab-sidebar">
    <nav class="cab-nav">
        <a href="{{ route('cabinet.subscription') }}" class="{{ $active === 'subscription' ? 'active' : '' }}">
            <span class="cab-nav-icon">◈</span>
            <span>Подписка</span>
        </a>
        <a href="{{ route('cabinet.trial') }}" class="{{ $active === 'trial' ? 'active' : '' }}">
            <span class="cab-nav-icon">▷</span>
            <span>Тест-драйв</span>
        </a>
        <div class="cab-nav-sep"></div>
        <a href="{{ route('cabinet.history') }}" class="{{ $active === 'history' ? 'active' : '' }}">
            <span class="cab-nav-icon">☰</span>
            <span>Покупки</span>
        </a>
        <a href="{{ route('cabinet.profile') }}" class="{{ $active === 'profile' ? 'active' : '' }}">
            <span class="cab-nav-icon">○</span>
            <span>Профиль</span>
        </a>
        <a href="{{ route('cabinet.security') }}" class="{{ $active === 'security' ? 'active' : '' }}">
            <span class="cab-nav-icon">◑</span>
            <span>Безопасность</span>
        </a>
        <a href="{{ route('cabinet.support.index') }}" class="{{ $active === 'support' ? 'active' : '' }}">
            <span class="cab-nav-icon">?</span>
            <span>Поддержка</span>
        </a>
    </nav>
    <div class="cab-nav-bottom">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="cab-nav-logout">
                <span class="cab-nav-icon">←</span>
                <span>Выйти</span>
            </button>
        </form>
    </div>
</aside>
