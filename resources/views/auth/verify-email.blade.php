<x-guest-layout title="{{ __('Подтвердите email') }}">
    <div class="auth-page">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="{{ config('app.brand_name') }}">
                <span>{{ config('app.brand_name') }} <em>{{ config('app.brand_suffix') }}</em></span>
            </a>
            <h1>Почта</h1>
            <p class="text-muted text-center" style="margin-bottom: 24px; font-size: 0.9rem; line-height: 1.6;">
                Спасибо за регистрацию! Перед началом работы подтвердите email, перейдя по ссылке из письма. Если письмо не пришло, мы отправим его повторно.
            </p>

            @if (session('status') == 'verification-link-sent')
                <p class="auth-flash" style="color: var(--red-light); margin-bottom: 16px; font-size: 0.9rem;">
                    Новая ссылка для подтверждения отправлена на email, указанный при регистрации.
                </p>
            @endif

            <div class="auth-actions" style="flex-direction: row; flex-wrap: wrap; gap: 12px; justify-content: center;">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Отправить ссылку ещё раз</button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Выйти</button>
                </form>
            </div>
            <div class="auth-links">
                <a href="{{ route('home') }}">Главная</a>
            </div>
        </div>
    </div>
</x-guest-layout>
