@extends('layouts.cabinet')

@section('title', 'Профиль')

@section('content')
    <h1 class="cab-page-title">Профиль</h1>
    <p class="cab-page-desc">Основная информация и настройки аккаунта.</p>

    <div class="cab-card">
        <div class="info-line">
            <span class="label">ID</span>
            <span class="value">#{{ $user->id }}</span>
        </div>
        <div class="info-line">
            <span class="label">Дата регистрации</span>
            <span class="value">{{ $user->created_at?->timezone(config('app.timezone'))->format('d.m.Y') ?? '—' }}</span>
        </div>
        <div class="info-line">
            <span class="label">Статус</span>
            <span class="value">Подписка не активна</span>
        </div>
    </div>

    <div style="height: 20px;"></div>

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Редактирование</span>
        </div>
        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="form-error" />
                </div>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 16px;">
                    {{ __('Your email address is unverified.') }}
                    <button type="submit" form="send-verification" style="background: none; border: none; color: var(--red-light); cursor: pointer; text-decoration: underline; padding: 0; font: inherit;">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p style="color: var(--red-light); font-size: 0.85rem; margin-bottom: 16px;">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                @if (session('status') === 'profile-updated')
                    <span style="margin-left: 12px; font-size: 0.85rem; color: var(--text-secondary);">{{ __('Saved.') }}</span>
                @endif
            </div>
        </form>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" style="display: none;">
        @csrf
    </form>
@endsection
