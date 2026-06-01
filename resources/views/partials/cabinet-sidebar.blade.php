@php
    $active = $activeRoute ?? '';
@endphp
<aside class="cab-sidebar" aria-label="Навигация кабинета">
    <div class="cab-sidebar-glow" aria-hidden="true"></div>

    <div class="cab-sidebar-inner">
        <nav class="cab-nav">
            <p class="cab-nav-group">Подключение</p>
            <a href="{{ route('cabinet.subscription') }}" class="cab-nav-link {{ $active === 'subscription' ? 'active' : '' }}">
                <span class="cab-nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                </span>
                <span class="cab-nav-text">Подписка</span>
            </a>
            <a href="{{ route('cabinet.trial') }}" class="cab-nav-link {{ $active === 'trial' ? 'active' : '' }}">
                <span class="cab-nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                    </svg>
                </span>
                <span class="cab-nav-text">Тест-драйв</span>
            </a>

            <p class="cab-nav-group">Аккаунт</p>
            <a href="{{ route('cabinet.history') }}" class="cab-nav-link {{ $active === 'history' ? 'active' : '' }}">
                <span class="cab-nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                        <path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </span>
                <span class="cab-nav-text">Покупки</span>
            </a>
            <a href="{{ route('cabinet.profile') }}" class="cab-nav-link {{ $active === 'profile' ? 'active' : '' }}">
                <span class="cab-nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <span class="cab-nav-text">Профиль</span>
            </a>
            <a href="{{ route('cabinet.security') }}" class="cab-nav-link {{ $active === 'security' ? 'active' : '' }}">
                <span class="cab-nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </span>
                <span class="cab-nav-text">Безопасность</span>
            </a>
            <a href="{{ route('cabinet.support.index') }}" class="cab-nav-link {{ $active === 'support' ? 'active' : '' }}">
                <span class="cab-nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </span>
                <span class="cab-nav-text">Поддержка</span>
            </a>
        </nav>
    </div>

    <div class="cab-nav-bottom">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="cab-nav-logout">
                <span class="cab-nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </span>
                <span class="cab-nav-text">Выйти</span>
            </button>
        </form>
    </div>
</aside>
