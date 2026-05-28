@extends('layouts.cabinet')

@section('title', 'Подписка')

@section('content')
    <h1 class="cab-page-title">Подписка</h1>
    <p class="cab-page-desc">Статус ваших активных тарифов.</p>

    @if(request()->has('order_id'))
        <div id="payment-status-banner" class="alert alert-info" role="status">
            Проверяем статус оплаты…
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($subscriptions->isNotEmpty())
        @foreach($subscriptions as $subscription)
            <div class="cab-card {{ !$loop->first ? 'mt-24' : '' }}">
                <div class="cab-card-header">
                    <span class="cab-card-title">
                        @if($subscriptions->count() > 1)
                            Подписка #{{ $loop->iteration }} — {{ $subscription->plan->name }}
                        @else
                            Текущий тариф
                        @endif
                    </span>
                    @if($subscription->isActive())
                        <span class="cab-badge green">Активна</span>
                    @elseif($subscription->isExpired())
                        <span class="cab-badge red">Истекла</span>
                    @else
                        <span class="cab-badge gray">Не активна</span>
                    @endif
                </div>

                <div class="sub-row">
                    <span class="sub-row-label">Тариф</span>
                    <span class="sub-row-value">{{ $subscription->plan->name }}</span>
                </div>
                <div class="sub-row">
                    <span class="sub-row-label">Действует до</span>
                    <span class="sub-row-value {{ $subscription->days_left <= 7 ? 'text-warning' : '' }}">
                        {{ $subscription->expires_at->format('d.m.Y') }}
                        @if($subscription->isActive())
                            <span class="days-left">({{ $subscription->days_left }} {{ trans_choice('дней|день|дня', $subscription->days_left) }})</span>
                        @endif
                    </span>
                </div>

                <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 12px;">
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Продлить подписку</a>
                </div>
            </div>
        @endforeach
    @elseif($activeTrialKey)
        @php $trialHours = (int) config('vpn.trial.duration_hours', 3); @endphp
        <div class="cab-card">
            <div class="cab-card-header">
                <span class="cab-card-title">Текущий тариф</span>
                <span class="cab-badge green">Активна</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Тариф</span>
                <span class="sub-row-value">Пробный доступ ({{ $trialHours }} {{ trans_choice('час|часа|часов', $trialHours) }})</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Действует до</span>
                <span class="sub-row-value">
                    {{ $activeTrialKey->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                    <span class="days-left">({{ $activeTrialKey->getRemainingTimeRu() }})</span>
                </span>
            </div>
            <p class="cab-page-desc" style="margin-top: 16px; margin-bottom: 0;">Те же серверы и подписочная ссылка, что и у платного тарифа — отличается только срок ({{ $trialHours }} ч).</p>
            <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 12px;">
                <a href="{{ route('home') }}#pricing" class="btn btn-primary btn-sm">Оформить подписку</a>
            </div>
        </div>
    @else
        <div class="cab-card">
            <div class="cab-card-header">
                <span class="cab-card-title">Текущий тариф</span>
                <span class="cab-badge gray">Не активна</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Тариф</span>
                <span class="sub-row-value muted">—</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Действует до</span>
                <span class="sub-row-value muted">—</span>
            </div>
            <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 12px;">
                <a href="{{ route('cabinet.trial') }}" class="btn btn-secondary btn-sm">Пробный доступ</a>
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Оформить подписку</a>
            </div>
        </div>
    @endif

    @include('partials.cabinet-subscription-link', ['connectionUri' => $connectionUri ?? null])

    <div class="mt-24">
        @include('partials.platform-instructions', [
            'subUrl' => $connectionUri ?? null,
            'title'  => 'Как подключиться',
            'desc'   => !empty($connectionUri)
                ? 'Скопируйте ссылку выше и добавьте её в VPN-приложение.'
                : 'После оформления подписки здесь появится ссылка для подключения.',
        ])
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
.alert {
    padding: 14px 18px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    font-size: 0.9rem;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.text-warning {
    color: #f59e0b !important;
}

.days-left {
    font-weight: 400;
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-left: 4px;
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
