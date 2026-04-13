<x-guest-layout title="{{ __('Создать аккаунт') }}">
    <div class="auth-page">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="{{ config('app.brand_name') }}">
                <span>{{ config('app.brand_name') }} <em>{{ config('app.brand_suffix') }}</em></span>
            </a>
            <h1>Создать аккаунт</h1>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('Как вас зовут') }}" required autofocus autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@mail.ru" required autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="password">Придумайте пароль</label>
                    <input type="password" id="password" name="password" placeholder="{{ __('От 8 символов') }}" required autocomplete="new-password">
                    <p class="form-hint">Если забудете — можно восстановить через почту.</p>
                    <x-input-error :messages="$errors->get('password')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="password_confirmation">{{ __('Повтор пароля') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="{{ __('Ещё раз') }}" required autocomplete="new-password">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="form-error" />
                </div>
                <label class="form-checkbox">
                    <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>
                    <span>Принимаю <a href="{{ route('agreement') }}" target="_blank">условия использования</a></span>
                </label>
                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
            <div class="auth-links">
                <a href="{{ route('login') }}">Уже есть аккаунт? Войти</a>
                <span class="separator"></span>
                <a href="{{ route('home') }}">Главная</a>
            </div>
        </div>
    </div>
</x-guest-layout>
