<x-guest-layout title="{{ __('Восстановление доступа') }}">
    <div class="auth-page">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="AVA VPN">
                <span>AVA <em>VPN</em></span>
            </a>
            <h1>Восстановление доступа</h1>
            <p class="text-muted text-center" style="margin-bottom: 24px; font-size: 0.9rem; line-height: 1.6;">
                Введите email, на который зарегистрирован аккаунт. Мы пришлём ссылку для смены пароля.
            </p>

            <x-auth-session-status class="auth-flash" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@mail.ru" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="form-error" />
                </div>
                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">Восстановить</button>
                </div>
            </form>
            <div class="auth-links">
                <a href="{{ route('login') }}">Назад ко входу</a>
                <span class="separator"></span>
                <a href="{{ route('home') }}">Главная</a>
            </div>
        </div>
    </div>
</x-guest-layout>
