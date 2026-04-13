<x-guest-layout title="{{ __('Подтвердите email') }}">
    <div class="auth-page">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="{{ config('app.brand_name') }}">
                <span>{{ config('app.brand_name') }} <em>{{ config('app.brand_suffix') }}</em></span>
            </a>
            <h1>Почта</h1>
            <p class="text-muted text-center" style="margin-bottom: 24px; font-size: 0.9rem; line-height: 1.6;">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>

            @if (session('status') == 'verification-link-sent')
                <p class="auth-flash" style="color: var(--red-light); margin-bottom: 16px; font-size: 0.9rem;">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </p>
            @endif

            <div class="auth-actions" style="flex-direction: row; flex-wrap: wrap; gap: 12px; justify-content: center;">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">{{ __('Resend Verification Email') }}</button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">{{ __('Log Out') }}</button>
                </form>
            </div>
            <div class="auth-links">
                <a href="{{ route('home') }}">Главная</a>
            </div>
        </div>
    </div>
</x-guest-layout>
