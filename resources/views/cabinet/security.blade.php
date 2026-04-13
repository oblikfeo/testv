@extends('layouts.cabinet')

@section('title', 'Безопасность')

@section('content')
    <h1 class="cab-page-title">Безопасность</h1>
    <p class="cab-page-desc">Смена пароля и управление аккаунтом.</p>

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Смена пароля</span>
        </div>
        <form method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')
            <div class="form-group">
                <label for="update_password_current_password">Текущий пароль</label>
                <input type="password" id="update_password_current_password" name="current_password" placeholder="Введите текущий пароль" autocomplete="current-password">
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="form-error" />
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="update_password_password">Новый пароль</label>
                    <input type="password" id="update_password_password" name="password" placeholder="Минимум 8 символов" autocomplete="new-password">
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="form-error" />
                </div>
                <div class="form-group">
                    <label for="update_password_password_confirmation">Ещё раз</label>
                    <input type="password" id="update_password_password_confirmation" name="password_confirmation" placeholder="Повторите" autocomplete="new-password">
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="form-error" />
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-sm">Сменить пароль</button>
                @if (session('status') === 'password-updated')
                    <span style="margin-left: 12px; font-size: 0.85rem; color: var(--text-secondary);">{{ __('Saved.') }}</span>
                @endif
            </div>
        </form>
    </div>

    <div style="height: 20px;"></div>

    <div class="cab-card danger">
        <div class="cab-card-header">
            <span class="cab-card-title">Удаление аккаунта</span>
        </div>
        <p class="danger-text">
            Все данные будут стёрты безвозвратно: подписка, ключи, история оплат. Это действие нельзя отменить.
        </p>
        <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('{{ __('Удалить аккаунт безвозвратно?') }}');">
            @csrf
            @method('delete')
            <div class="form-group">
                <label for="delete_password">{{ __('Password') }}</label>
                <input type="password" id="delete_password" name="password" placeholder="{{ __('Введите пароль для подтверждения') }}" required>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="form-error" />
            </div>
            <button type="submit" class="btn btn-danger btn-sm">Удалить аккаунт</button>
        </form>
    </div>
@endsection
