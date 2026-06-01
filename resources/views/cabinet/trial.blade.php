@extends('layouts.cabinet')

@section('title', 'Тест-драйв')

@php
    $trialHours = (int) config('vpn.trial.duration_hours', 3);
    $isActive = $trialKey && $trialKey->isActive();
    $timeProgress = 0;
    if ($isActive && $trialKey->activated_at) {
        $totalMinutes = max(1, $trialHours * 60);
        $elapsed = (int) $trialKey->activated_at->diffInMinutes(now());
        $timeProgress = (int) max(0, min(100, round((1 - $elapsed / $totalMinutes) * 100)));
    }
    $trafficProgress = $trialKey ? (100 - $trialKey->getUsagePercent()) : 0;
    $showTraffic = $trialKey && $trialKey->total_bytes > 0;
@endphp

@section('content')
<div class="trial-page">
    <header class="trial-hero">
        <h1 class="cab-page-title">Тест-драйв</h1>
        <p class="cab-page-desc">
            Бесплатный доступ на {{ $trialHours }} {{ trans_choice('час|часа|часов', $trialHours) }} —
            те же серверы и подписочная ссылка, что у платного тарифа. Один раз на аккаунт.
        </p>
    </header>

    @if(session('success'))
        <div class="sub-alert sub-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="sub-alert sub-alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="trial-stack">
        @if(!$user->hasVerifiedEmail())
            <article class="cab-card trial-card trial-card--warn">
                <div class="trial-card-icon trial-card-icon--warn" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <path d="m22 6-10 7L2 6"/>
                    </svg>
                </div>
                <h2 class="trial-card-title">Подтвердите email</h2>
                <p class="trial-card-desc">
                    Чтобы активировать пробный доступ, перейдите по ссылке из письма после регистрации.
                </p>
                <form method="POST" action="{{ route('verification.send') }}" class="trial-card-action">
                    @csrf
                    <button type="submit" class="btn btn-primary trial-cta">Отправить письмо повторно</button>
                </form>
            </article>

        @elseif($isActive)
            <article class="cab-card trial-card trial-card--active">
                <div class="trial-card-top">
                    <div class="trial-card-title-wrap">
                        <span class="cab-badge green">Активна</span>
                        <h2 class="trial-card-title">Пробная подписка</h2>
                    </div>
                </div>

                <div class="trial-stats">
                    <div class="trial-stat">
                        <span class="trial-stat-label">Действует до</span>
                        <span class="trial-stat-value">{{ $trialKey->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="trial-stat">
                        <span class="trial-stat-label">Осталось</span>
                        <span class="trial-stat-value {{ $timeProgress <= 20 ? 'is-warn' : '' }}">{{ $trialKey->getRemainingTimeRu() }}</span>
                    </div>
                    @if($showTraffic)
                        <div class="trial-stat">
                            <span class="trial-stat-label">Трафик</span>
                            <span class="trial-stat-value">{{ $trialKey->getRemainingGb() }} / {{ $trialKey->getTotalGb() }} ГБ</span>
                        </div>
                    @endif
                </div>

                <div class="trial-progress-block">
                    <div class="trial-progress-head">
                        <span>Время доступа</span>
                        <span>{{ $timeProgress }}%</span>
                    </div>
                    <div class="trial-progress" role="progressbar" aria-valuenow="{{ $timeProgress }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="trial-progress-fill {{ $timeProgress <= 20 ? 'is-warn' : '' }}" style="width: {{ $timeProgress }}%"></div>
                    </div>
                </div>

                @if($showTraffic)
                    <div class="trial-progress-block">
                        <div class="trial-progress-head">
                            <span>Остаток трафика</span>
                            <span>{{ $trafficProgress }}%</span>
                        </div>
                        <div class="trial-progress" role="progressbar" aria-valuenow="{{ $trafficProgress }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="trial-progress-fill {{ $trafficProgress <= 20 ? 'is-warn' : '' }}" style="width: {{ $trafficProgress }}%"></div>
                        </div>
                    </div>
                @endif

                @include('partials.cabinet-subscription-link', ['connectionUri' => $connectionUri ?? null])

                <div class="trial-card-actions">
                    <a href="{{ route('cabinet.subscription') }}" class="btn btn-secondary trial-cta">Раздел «Подписка»</a>
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary trial-cta">Оформить подписку</a>
                </div>
            </article>

        @elseif($trialKey)
            <article class="cab-card trial-card">
                <div class="trial-card-top">
                    <div class="trial-card-title-wrap">
                        <span class="cab-badge gray">Истекла</span>
                        <h2 class="trial-card-title">Пробный период завершён</h2>
                    </div>
                </div>
                <p class="trial-card-desc">
                    Доступ закончился {{ $trialKey->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}.
                    Оформите платный тариф — подключение по той же ссылке, что и в пробном режиме.
                </p>
                <div class="trial-card-actions">
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary trial-cta">Выбрать тариф</a>
                </div>
            </article>

        @elseif($canUseTrial)
            <article class="cab-card trial-card trial-card--offer">
                <div class="trial-card-top">
                    <div class="trial-card-title-wrap">
                        <span class="cab-badge green">Доступно</span>
                        <h2 class="trial-card-title">Попробуйте бесплатно</h2>
                    </div>
                </div>

                <ul class="trial-features">
                    <li>
                        <span class="trial-feature-icon" aria-hidden="true">✓</span>
                        <span>{{ $trialHours }} {{ trans_choice('час|часа|часов', $trialHours) }} полного доступа</span>
                    </li>
                    <li>
                        <span class="trial-feature-icon" aria-hidden="true">✓</span>
                        <span>Серверы Hysteria2 + VLESS, как у платной подписки</span>
                    </li>
                    <li>
                        <span class="trial-feature-icon" aria-hidden="true">✓</span>
                        <span>Одна подписочная ссылка для Happ и v2RayTun</span>
                    </li>
                </ul>

                <form method="POST" action="{{ route('cabinet.trial.create') }}" class="trial-card-action">
                    @csrf
                    <button type="submit" class="btn btn-primary trial-cta trial-cta--lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        Активировать пробную подписку
                    </button>
                </form>
            </article>

        @else
            <article class="cab-card trial-card">
                <div class="trial-card-top">
                    <div class="trial-card-title-wrap">
                        <span class="cab-badge gray">Использовано</span>
                        <h2 class="trial-card-title">Тест-драйв уже был</h2>
                    </div>
                </div>
                <p class="trial-card-desc">
                    На этом аккаунте пробный период уже активировали. Выберите платный тариф — подключение займёт пару минут.
                </p>
                <div class="trial-card-actions">
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary trial-cta">Перейти к покупкам</a>
                </div>
            </article>
        @endif

        @if($user->hasVerifiedEmail())
            <div class="trial-setup">
                @include('partials.platform-instructions', [
                    'subUrl' => $connectionUri ?? null,
                    'title'  => 'Как подключиться',
                    'desc'   => $isActive
                        ? 'Выберите платформу — ссылка подставится в кнопки «Добавить в Happ / v2RayTun».'
                        : 'После активации пробного доступа здесь появится ссылка и пошаговая инструкция.',
                ])
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.cabinet-copy-sub-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(this.getAttribute('data-copy-target'));
        if (!input) return;
        var label = this.querySelector('.sub-link-copy-text');
        navigator.clipboard.writeText(input.value).then(function () {
            if (label) label.textContent = 'Скопировано';
            setTimeout(function () { if (label) label.textContent = 'Копировать'; }, 2000);
        });
    });
});
</script>
@endpush
