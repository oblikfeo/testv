@extends('layouts.cabinet')

@section('title', 'Безопасность')

@section('content')
<div class="account-page security-page">
    <header class="account-hero">
        <h1 class="cab-page-title">Безопасность</h1>
        <p class="cab-page-desc">Смена пароля и необратимые действия с аккаунтом.</p>
    </header>

    <div class="account-stack">
        <section class="cab-card account-card account-card--form" aria-labelledby="security-password-title">
            <div class="account-section-head">
                <div class="account-section-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <div>
                    <h2 id="security-password-title" class="account-section-title">Смена пароля</h2>
                    <p class="account-section-desc">Используйте надёжный пароль длиной от 8 символов</p>
                </div>
            </div>

            <form method="post" action="{{ route('password.update') }}" class="account-form">
                @csrf
                @method('put')

                <div class="account-field">
                    <label for="update_password_current_password">Текущий пароль</label>
                    <input type="password" id="update_password_current_password" name="current_password" placeholder="Введите текущий пароль" autocomplete="current-password">
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="form-error" />
                </div>

                <div class="account-form-grid">
                    <div class="account-field">
                        <label for="update_password_password">Новый пароль</label>
                        <input type="password" id="update_password_password" name="password" placeholder="Минимум 8 символов" autocomplete="new-password">
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="form-error" />
                    </div>
                    <div class="account-field">
                        <label for="update_password_password_confirmation">Подтверждение</label>
                        <input type="password" id="update_password_password_confirmation" name="password_confirmation" placeholder="Повторите пароль" autocomplete="new-password">
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="form-error" />
                    </div>
                </div>

                <div class="account-form-footer">
                    <button type="submit" class="btn btn-primary account-submit">Сменить пароль</button>
                    @if(session('status') === 'password-updated')
                        <span class="account-saved" role="status">Пароль обновлён</span>
                    @endif
                </div>
            </form>
        </section>

        <section class="cab-card account-card account-card--danger" aria-labelledby="security-delete-title">
            <div class="account-section-head account-section-head--danger">
                <div class="account-section-icon account-section-icon--danger" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                        <line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>
                    </svg>
                </div>
                <div>
                    <h2 id="security-delete-title" class="account-section-title">Удаление аккаунта</h2>
                    <p class="account-section-desc account-section-desc--danger">
                        Будут удалены подписка, ключи, история оплат и все данные профиля. Восстановить аккаунт нельзя.
                    </p>
                </div>
            </div>

            <form
                method="post"
                action="{{ route('profile.destroy') }}"
                class="account-form account-delete-form"
                onsubmit="return confirm('Удалить аккаунт безвозвратно? Это действие нельзя отменить.');"
            >
                @csrf
                @method('delete')

                <div class="account-field">
                    <label for="delete_password">Подтвердите паролем</label>
                    <input type="password" id="delete_password" name="password" placeholder="Текущий пароль для подтверждения" autocomplete="current-password" required>
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="form-error" />
                </div>

                <div class="account-form-footer">
                    <button type="submit" class="btn btn-danger account-submit">Удалить аккаунт</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
