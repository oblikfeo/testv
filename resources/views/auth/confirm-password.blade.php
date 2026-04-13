<x-guest-layout title="{{ __('Подтверждение пароля') }}">
    <div class="auth-page">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="{{ config('app.brand_name') }}">
                <span>{{ config('app.brand_name') }} <em>{{ config('app.brand_suffix') }}</em></span>
            </a>
            <h1>Подтверждение</h1>
            <p class="text-muted text-center" style="margin-bottom: 24px; font-size: 0.9rem; line-height: 1.6;">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <x-input-error :messages="$errors->get('password')" class="form-error" />
                </div>
                <div class="auth-actions">
                    <button type="submit" class="btn btn-primary">{{ __('Confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
