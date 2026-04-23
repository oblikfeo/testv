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

            @if (config('services.telegram_bot.username') && config('services.telegram_bot.token'))
                <div class="auth-divider" style="margin: 18px 0; text-align: center; opacity: .75;">
                    <span>или</span>
                </div>
                @if ($errors->has('telegram'))
                    <div class="auth-flash" style="margin-bottom: 12px;">
                        {{ $errors->first('telegram') }}
                    </div>
                @endif
                <div style="display:flex; justify-content:center;">
                    <script async src="https://telegram.org/js/telegram-widget.js?22"
                        data-telegram-login="{{ config('services.telegram_bot.username') }}"
                        data-size="large"
                        data-radius="10"
                        data-request-access="write"
                        data-auth-url="{{ route('auth.telegram.callback') }}">
                    </script>
                </div>
            @endif
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
