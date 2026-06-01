@extends('layouts.cabinet')

@section('title', 'Подписка')

@section('content')
<div class="sub-page">
    <header class="sub-hero">
        <h1 class="cab-page-title">Подписка</h1>
        <p class="cab-page-desc">Тариф, срок действия и ссылка для подключения в одном месте.</p>
    </header>

    @if(request()->has('order_id'))
        <div id="payment-status-banner" class="sub-alert sub-alert-info" role="status">
            Проверяем статус оплаты…
        </div>
    @endif

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

    <div class="sub-stack">
        @if($subscriptions->isNotEmpty())
            @foreach($subscriptions as $subscription)
                <article class="cab-card sub-card">
                    <div class="sub-card-top">
                        <div class="sub-card-title-wrap">
                            @if($subscription->isActive())
                                <span class="cab-badge green">Активна</span>
                            @elseif($subscription->isExpired())
                                <span class="cab-badge red">Истекла</span>
                            @else
                                <span class="cab-badge gray">Не активна</span>
                            @endif
                            <h2 class="sub-plan">{{ $subscription->plan->name }}</h2>
                        </div>
                    </div>

                    <div class="sub-stats">
                        <div class="sub-stat">
                            <span class="sub-stat-label">Действует до</span>
                            <span class="sub-stat-value {{ $subscription->days_left <= 7 && $subscription->isActive() ? 'is-warn' : '' }}">
                                {{ $subscription->expires_at->format('d.m.Y') }}
                            </span>
                        </div>
                        @if($subscription->isActive())
                            <div class="sub-stat">
                                <span class="sub-stat-label">Осталось</span>
                                <span class="sub-stat-value">
                                    {{ $subscription->days_left }} {{ trans_choice('дней|день|дня', $subscription->days_left) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @if($loop->first)
                        @include('partials.cabinet-subscription-link', ['connectionUri' => $connectionUri ?? null])
                    @endif

                    <div class="sub-actions">
                        <a href="{{ route('cabinet.history') }}" class="btn btn-primary sub-cta">Продлить подписку</a>
                    </div>
                </article>
            @endforeach
        @elseif($activeTrialKey)
            <article class="cab-card sub-card">
                <div class="sub-card-top">
                    <div class="sub-card-title-wrap">
                        <span class="cab-badge green">Активна</span>
                        <h2 class="sub-plan">Пробный доступ</h2>
                    </div>
                </div>

                <div class="sub-stats">
                    <div class="sub-stat">
                        <span class="sub-stat-label">Действует до</span>
                        <span class="sub-stat-value">
                            {{ $activeTrialKey->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                        </span>
                    </div>
                    <div class="sub-stat">
                        <span class="sub-stat-label">Осталось</span>
                        <span class="sub-stat-value">{{ $activeTrialKey->getRemainingTimeRu() }}</span>
                    </div>
                </div>

                @include('partials.cabinet-subscription-link', ['connectionUri' => $connectionUri ?? null])

                <div class="sub-actions">
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary sub-cta">Оформить подписку</a>
                </div>
            </article>
        @else
            <article class="cab-card sub-card sub-card-empty">
                <div class="sub-card-top">
                    <div class="sub-card-title-wrap">
                        <span class="cab-badge gray">Не активна</span>
                        <h2 class="sub-plan">Нет активного тарифа</h2>
                    </div>
                </div>
                <p class="sub-empty-desc">Оформите подписку или активируйте бесплатный пробный доступ на 3 часа.</p>
                <div class="sub-actions sub-actions-split">
                    <a href="{{ route('cabinet.trial') }}" class="btn btn-secondary sub-cta">Пробный доступ</a>
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary sub-cta">Оформить подписку</a>
                </div>
            </article>
        @endif

        <div class="sub-setup">
            @include('partials.platform-instructions', [
                'subUrl' => $connectionUri ?? null,
                'title'  => 'Как подключиться',
                'desc'   => !empty($connectionUri)
                    ? 'Выберите платформу и следуйте шагам — ссылку можно скопировать выше или добавить в приложение одной кнопкой.'
                    : 'После оформления подписки здесь появится ссылка и пошаговая инструкция.',
            ])
        </div>
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
            else btn.textContent = 'Скопировано';
            setTimeout(function () {
                if (label) label.textContent = 'Копировать';
            }, 2000);
        });
    });
});
</script>
@endpush

@push('scripts')
@if(request()->has('order_id'))
<script>
(function () {
    const orderId = @json(request('order_id'));
    const statusUrl = @json(route('payment.status')) + '?order_id=' + encodeURIComponent(orderId);
    const banner = document.getElementById('payment-status-banner');
    const maxAttempts = 30;
    let attempts = 0;

    function cleanUrl() {
        const url = new URL(window.location.href);
        url.searchParams.delete('order_id');
        window.history.replaceState({}, '', url.pathname + url.search);
    }

    function setBanner(className, text) {
        if (!banner) return;
        banner.className = 'sub-alert ' + className;
        banner.textContent = text;
    }

    function poll() {
        attempts += 1;
        fetch(statusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'fulfilled') {
                    setBanner('sub-alert-success', 'Оплата прошла успешно. Обновляем кабинет…');
                    cleanUrl();
                    window.setTimeout(function () { window.location.reload(); }, 1200);
                    return;
                }
                if (data.status === 'cancelled') {
                    setBanner('sub-alert-error', 'Платёж отменён. Можно попробовать снова в разделе «Покупки».');
                    cleanUrl();
                    return;
                }
                if (attempts >= maxAttempts) {
                    setBanner('sub-alert-info', 'Оплата ещё обрабатывается. Обновите страницу через минуту.');
                    cleanUrl();
                    return;
                }
                window.setTimeout(poll, 2000);
            })
            .catch(function () {
                if (attempts >= maxAttempts) {
                    setBanner('sub-alert-info', 'Не удалось проверить оплату. Обновите страницу.');
                    cleanUrl();
                    return;
                }
                window.setTimeout(poll, 2000);
            });
    }

    poll();
})();
</script>
@endif
@endpush
