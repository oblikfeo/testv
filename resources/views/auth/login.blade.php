<x-guest-layout title="{{ __('Войти в аккаунт') }}">
    <div class="auth-page">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="AVA VPN">
                <span>AVA <em>VPN</em></span>
            </a>
            <h1>Войти в аккаунт</h1>

            <x-auth-session-status class="auth-flash" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@mail.ru" required autofocus autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" placeholder="{{ __('Введите пароль') }}" required autocomplete="current-password">
                    <x-input-error :messages="$errors->get('password')" class="form-error" />
                </div>
                <div class="form-row-between">
                    <label class="form-checkbox" style="margin-bottom: 0;">
                        <input type="checkbox" name="remember">
                        <span>Не выходить</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Не помню пароль</a>
                    @endif
                </div>
                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">Войти</button>
                </div>
            </form>
            <div class="auth-links">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Нет аккаунта? Создать</a>
                    <span class="separator"></span>
                @endif
                <a href="{{ route('home') }}">Главная</a>
            </div>
        </div>
    </div>
</x-guest-layout>
