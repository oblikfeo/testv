<x-guest-layout title="{{ __('Новый пароль') }}">
    <div class="auth-page">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="{{ config('app.brand_name') }}">
                <span>{{ config('app.brand_name') }} <em>{{ config('app.brand_suffix') }}</em></span>
            </a>
            <h1>Новый пароль</h1>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="password">Новый пароль</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    <x-input-error :messages="$errors->get('password')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="password_confirmation">{{ __('Повтор пароля') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="form-error" />
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">Сохранить пароль</button>
                </div>
            </form>
            <div class="auth-links">
                <a href="{{ route('login') }}">Войти</a>
            </div>
        </div>
    </div>
</x-guest-layout>
