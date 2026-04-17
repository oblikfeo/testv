@extends('layouts.cabinet')

@section('title', 'Тест-драйв')

@push('styles')
<style>
    .trial-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .trial-stat {
        background: rgba(255,255,255,0.03);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
    }
    .trial-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--accent);
        margin-bottom: 4px;
    }
    .trial-stat-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }
    .trial-stat.expired .trial-stat-value {
        color: #ef4444;
    }
    .trial-stat.warning .trial-stat-value {
        color: #f59e0b;
    }
    .progress-bar {
        height: 8px;
        background: rgba(255,255,255,0.1);
        border-radius: 4px;
        overflow: hidden;
        margin: 16px 0;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent), #8b5cf6);
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    .progress-fill.warning {
        background: linear-gradient(90deg, #f59e0b, #ef4444);
    }
    .sub-link-box {
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 16px;
        margin-top: 20px;
    }
    .sub-link-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sub-link-label svg {
        width: 16px;
        height: 16px;
    }
    .sub-link-row {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .sub-link-input {
        flex: 1;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 12px 14px;
        color: var(--text-primary);
        font-family: monospace;
        font-size: 0.85rem;
    }
    .country-flag {
        font-size: 1.2rem;
        margin-right: 6px;
    }
    .trial-name {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 12px;
    }
    .trial-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .trial-badge.active {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }
    .trial-badge.expired {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    .info-text {
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    .info-text strong {
        color: var(--text-primary);
    }
    .apps-hint {
        margin-top: 20px;
        padding: 16px;
        background: rgba(139, 92, 246, 0.1);
        border-radius: 12px;
        border: 1px solid rgba(139, 92, 246, 0.2);
    }
    .apps-hint-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .apps-hint p {
        color: var(--text-secondary);
        font-size: 0.85rem;
        margin: 0;
    }
    .apps-hint a {
        color: var(--accent);
        text-decoration: underline;
    }
    .verify-notice {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .verify-notice p {
        color: var(--text-secondary);
        margin-bottom: 16px;
    }
</style>
@endpush

@push('scripts')
<script>
    function copySubLink() {
        var input = document.getElementById('subLinkInput');
        navigator.clipboard.writeText(input.value);
        var btn = document.getElementById('copyBtn');
        btn.textContent = 'Скопировано!';
        btn.classList.add('btn-success');
        setTimeout(function() {
            btn.textContent = 'Копировать';
            btn.classList.remove('btn-success');
        }, 2000);
    }
</script>
@endpush

@section('content')
    <h1 class="cab-page-title">Тест-драйв</h1>
    <p class="cab-page-desc">Бесплатный ключ на 8 часов и 10 ГБ, чтобы проверить сервис перед покупкой. Выдаётся один раз на аккаунт.</p>

    @if (session('success'))
        <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 20px; color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 20px; color: #ef4444;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="cab-card">
        @if (!$user->hasVerifiedEmail())
            {{-- Email не подтверждён --}}
            <div class="verify-notice">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="margin-bottom: 12px;">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p>Для получения тестового ключа необходимо подтвердить email</p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Отправить письмо повторно</button>
                </form>
            </div>

        @elseif ($trialKey)
            {{-- Ключ уже выдан --}}
            <div class="trial-name">
                <span class="country-flag">🇷🇺</span>
                AVA тестовый период
                @if ($trialKey->isActive())
                    <span class="trial-badge active">● Активен</span>
                @else
                    <span class="trial-badge expired">● Истёк</span>
                @endif
            </div>

            <div class="trial-stats">
                <div class="trial-stat {{ $trialKey->isExpired() ? 'expired' : '' }}">
                    <div class="trial-stat-value">
                        @if ($trialKey->isExpired())
                            Истёк
                        @else
                            {{ $trialKey->expires_at->diffForHumans(null, true) }}
                        @endif
                    </div>
                    <div class="trial-stat-label">Осталось времени</div>
                </div>
                <div class="trial-stat {{ $trialKey->getUsagePercent() > 80 ? 'warning' : '' }}">
                    <div class="trial-stat-value">{{ $trialKey->getRemainingGb() }} ГБ</div>
                    <div class="trial-stat-label">Осталось трафика</div>
                </div>
                <div class="trial-stat">
                    <div class="trial-stat-value">{{ $trialKey->getUsedGb() }} ГБ</div>
                    <div class="trial-stat-label">Использовано</div>
                </div>
            </div>

            <div style="margin-bottom: 8px; display: flex; justify-content: space-between; font-size: 0.85rem;">
                <span style="color: var(--text-secondary);">Трафик</span>
                <span style="color: var(--text-primary);">{{ $trialKey->getUsedGb() }} / {{ $trialKey->getTotalGb() }} ГБ</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill {{ $trialKey->getUsagePercent() > 80 ? 'warning' : '' }}" style="width: {{ $trialKey->getUsagePercent() }}%;"></div>
            </div>

            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0;">
                Действует до: <strong style="color: var(--text-primary);">{{ $trialKey->expires_at->format('d.m.Y H:i') }}</strong>
            </p>

            <div class="sub-link-box">
                <div class="sub-link-label">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                    Подписочная ссылка (для Happ, Hiddify, V2rayN)
                </div>
                <div class="sub-link-row">
                    <input type="text" 
                           id="subLinkInput" 
                           class="sub-link-input" 
                           value="{{ url('/sub/' . $trialKey->sub_id) }}" 
                           readonly>
                    <button id="copyBtn" class="btn btn-primary btn-sm" onclick="copySubLink()">Копировать</button>
                </div>
            </div>

            <div class="apps-hint">
                <div class="apps-hint-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4"/>
                        <path d="M12 8h.01"/>
                    </svg>
                    Как подключиться?
                </div>
                <p>Скопируйте ссылку и добавьте её в приложение как «Подписку». Рекомендуем <a href="https://apps.apple.com/app/happ/id6504287215" target="_blank">Happ</a> для iOS или <a href="https://play.google.com/store/apps/details?id=app.hiddify.com" target="_blank">Hiddify</a> для Android.</p>
            </div>

        @elseif ($canUseTrial)
            {{-- Можно получить ключ --}}
            <div class="cab-card-header">
                <span class="cab-card-title">Тестовый ключ</span>
                <span class="cab-badge" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">Доступно</span>
            </div>
            <p class="info-text">
                Нажмите кнопку — мы создадим для вас тестовую подписку на <strong>8 часов</strong> и <strong>10 ГБ</strong> трафика. 
                Вы получите ссылку, которую нужно добавить в VPN-приложение.
            </p>
            <form method="POST" action="{{ route('cabinet.trial.create') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Получить тестовый ключ
                </button>
            </form>

        @else
            {{-- Trial уже использован --}}
            <div class="cab-card-header">
                <span class="cab-card-title">Тестовый ключ</span>
                <span class="cab-badge gray">Использовано</span>
            </div>
            <p class="info-text">
                Вы уже использовали тестовый период. Оформите подписку, чтобы продолжить пользоваться сервисом без ограничений.
            </p>
            <a href="{{ route('home') }}#pricing" class="btn btn-primary">Оформить подписку</a>
        @endif
    </div>
@endsection
