@extends('layouts.cabinet')

@section('title', 'Подписка')

@section('content')
<div class="sub-page">
    <div class="sub-head">
        <h1 class="cab-page-title">Подписка</h1>
        <p class="cab-page-desc">Ваш тариф и ссылка для подключения.</p>
    </div>

    @if(request()->has('order_id'))
        <div id="payment-status-banner" class="alert alert-info" role="status">
            Проверяем статус оплаты…
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($subscriptions->isNotEmpty())
        @foreach($subscriptions as $subscription)
            <div class="cab-card sub-card">
                @if($subscription->isActive())
                    <span class="cab-badge green">Активна</span>
                @elseif($subscription->isExpired())
                    <span class="cab-badge red">Истекла</span>
                @else
                    <span class="cab-badge gray">Не активна</span>
                @endif

                <h2 class="sub-plan">{{ $subscription->plan->name }}</h2>
                <p class="sub-meta">
                    Действует до
                    <span class="{{ $subscription->days_left <= 7 ? 'warn' : '' }}">{{ $subscription->expires_at->format('d.m.Y') }}</span>
                    @if($subscription->isActive())
                        · {{ $subscription->days_left }} {{ trans_choice('дней|день|дня', $subscription->days_left) }}
                    @endif
                </p>

                @if($loop->first)
                    @include('partials.cabinet-subscription-link', ['connectionUri' => $connectionUri ?? null])
                @endif

                <div class="sub-actions">
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Продлить подписку</a>
                </div>
            </div>
        @endforeach
    @elseif($activeTrialKey)
        @php $trialHours = (int) config('vpn.trial.duration_hours', 3); @endphp
        <div class="cab-card sub-card">
            <span class="cab-badge green">Активна</span>

            <h2 class="sub-plan">Пробный доступ</h2>
            <p class="sub-meta">
                Действует до
                {{ $activeTrialKey->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                · {{ $activeTrialKey->getRemainingTimeRu() }}
            </p>

            @include('partials.cabinet-subscription-link', ['connectionUri' => $connectionUri ?? null])

            <div class="sub-actions">
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Оформить подписку</a>
            </div>
        </div>
    @else
        <div class="cab-card sub-card">
            <span class="cab-badge gray">Не активна</span>

            <h2 class="sub-plan">Нет активного тарифа</h2>
            <p class="sub-meta">Оформите подписку или активируйте бесплатный пробный доступ.</p>

            <div class="sub-actions">
                <a href="{{ route('cabinet.trial') }}" class="btn btn-secondary btn-sm">Пробный доступ</a>
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Оформить подписку</a>
            </div>
        </div>
    @endif

    <div class="mt-24">
        @include('partials.platform-instructions', [
            'subUrl' => $connectionUri ?? null,
            'title'  => 'Как подключиться',
            'desc'   => !empty($connectionUri)
                ? 'Скопируйте ссылку выше и добавьте её в Happ или v2RayTun.'
                : 'После оформления подписки здесь появится ссылка для подключения.',
        ])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.cabinet-copy-sub-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(this.getAttribute('data-copy-target'));
        if (!input) return;
        navigator.clipboard.writeText(input.value);
        this.textContent = 'Скопировано!';
        var self = this;
        setTimeout(function () { self.textContent = 'Копировать'; }, 2000);
    });
});
</script>
@endpush

@push('styles')
<style>
.sub-page {
    max-width: 600px;
    margin: 0 auto;
}

.sub-head {
    text-align: center;
    margin-bottom: 24px;
}
.sub-head .cab-page-title { margin-bottom: 6px; }
.sub-head .cab-page-desc { margin-bottom: 0; }

.sub-card {
    text-align: center;
}
.sub-card + .sub-card { margin-top: 16px; }
.sub-card .cab-badge { display: inline-block; }

.sub-plan {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 14px 0 6px;
}

.sub-meta {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.5;
}
.sub-meta .warn { color: #f59e0b; }

.sub-link {
    margin-top: 22px;
    padding-top: 22px;
    border-top: 1px solid var(--border-color);
}
.sub-link-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-bottom: 10px;
}
.sub-link-row {
    display: flex;
    gap: 10px;
    align-items: stretch;
}
.sub-link-input {
    flex: 1;
    min-width: 0;
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 0.8rem;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-secondary);
}
.sub-link-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin: 10px 0 0;
    line-height: 1.5;
}

.sub-actions {
    margin-top: 22px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
}

.alert {
    padding: 14px 18px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    font-size: 0.9rem;
    text-align: center;
}
.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    color: #22c55e;
}
.alert-info {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.25);
    color: #93c5fd;
}
.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.25);
    color: #f87171;
}

@media (max-width: 640px) {
    .sub-link-row { flex-direction: column; }
}
</style>
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
        banner.className = 'alert ' + className;
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
                    setBanner('alert-success', 'Оплата прошла успешно. Обновляем кабинет…');
                    cleanUrl();
                    window.setTimeout(function () { window.location.reload(); }, 1200);
                    return;
                }
                if (data.status === 'cancelled') {
                    setBanner('alert-error', 'Платёж отменён. Можно попробовать снова в разделе «Покупки».');
                    cleanUrl();
                    return;
                }
                if (attempts >= maxAttempts) {
                    setBanner('alert-info', 'Оплата ещё обрабатывается. Обновите страницу через минуту.');
                    cleanUrl();
                    return;
                }
                window.setTimeout(poll, 2000);
            })
            .catch(function () {
                if (attempts >= maxAttempts) {
                    setBanner('alert-info', 'Не удалось проверить оплату. Обновите страницу.');
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
