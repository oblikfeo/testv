@extends('layouts.cabinet')

@section('title', 'Профиль')

@section('content')
<div class="account-page profile-page">
    <header class="account-hero">
        <h1 class="cab-page-title">Профиль</h1>
        <p class="cab-page-desc">Основная информация об аккаунте и настройки.</p>
    </header>

    <div class="account-stack">
        <section class="cab-card account-card account-card--overview" aria-label="Сводка аккаунта">
            <div class="account-card-head">
                <div class="account-avatar" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr($user->name ?: $user->email, 0, 1)) }}
                </div>
                <div class="account-identity">
                    <h2 class="account-card-title">{{ $user->name ?: 'Пользователь' }}</h2>
                    <p class="account-card-sub">{{ $user->email }}</p>
                </div>
            </div>

            <div class="account-stats">
                <div class="account-stat">
                    <span class="account-stat-label">ID аккаунта</span>
                    <span class="account-stat-value">#{{ $user->id }}</span>
                </div>
                <div class="account-stat">
                    <span class="account-stat-label">Регистрация</span>
                    <span class="account-stat-value">{{ $user->created_at?->timezone(config('app.timezone'))->format('d.m.Y') ?? '—' }}</span>
                </div>
                <div class="account-stat">
                    <span class="account-stat-label">Email</span>
                    <span class="account-stat-value">
                        @if($user->hasVerifiedEmail())
                            <span class="account-pill is-ok">Подтверждён</span>
                        @else
                            <span class="account-pill is-warn">Не подтверждён</span>
                        @endif
                    </span>
                </div>
                <div class="account-stat">
                    <span class="account-stat-label">Доступ VPN</span>
                    <span class="account-stat-value">
                        @if($hasActiveAccess)
                            <span class="account-pill is-ok">Активен</span>
                        @else
                            <span class="account-pill is-muted">Нет подписки</span>
                        @endif
                    </span>
                </div>
                @if($user->telegram_username || $user->telegram_id)
                    <div class="account-stat account-stat--wide">
                        <span class="account-stat-label">Telegram</span>
                        <span class="account-stat-value">
                            @if($user->telegram_username)
                                {{ '@' . ltrim($user->telegram_username, '@') }}
                            @else
                                ID {{ $user->telegram_id }}
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </section>

        <section class="cab-card account-card account-card--form" aria-labelledby="profile-edit-title">
            <div class="account-section-head">
                <h2 id="profile-edit-title" class="account-section-title">Редактирование</h2>
                <p class="account-section-desc">Имя и email для входа и уведомлений</p>
            </div>

            @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="account-notice account-notice--warn">
                    <p>Email ещё не подтверждён — часть функций (включая тест-драйв) недоступна.</p>
                    <button type="submit" form="send-verification" class="account-notice-link">Отправить ссылку ещё раз</button>
                </div>
                @if(session('status') === 'verification-link-sent')
                    <div class="sub-alert sub-alert-success">Новая ссылка подтверждения отправлена на ваш email.</div>
                @endif
            @endif

            <form method="post" action="{{ route('profile.update') }}" class="account-form">
                @csrf
                @method('patch')

                <div class="account-form-grid">
                    <div class="account-field">
                        <label for="name">Имя</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Как вас зовут" autocomplete="name">
                        <x-input-error :messages="$errors->get('name')" class="form-error" />
                    </div>
                    <div class="account-field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="name@mail.ru" autocomplete="email">
                        <x-input-error :messages="$errors->get('email')" class="form-error" />
                    </div>
                </div>

                <div class="account-form-footer">
                    <button type="submit" class="btn btn-primary account-submit">Сохранить изменения</button>
                    @if(session('status') === 'profile-updated')
                        <span class="account-saved" role="status">Сохранено</span>
                    @endif
                </div>
            </form>
        </section>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="sr-only">
        @csrf
    </form>
</div>
@endsection
