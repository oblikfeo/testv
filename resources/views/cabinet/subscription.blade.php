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

    @php
        $primarySaleKey = collect($saleKeys)->first();
        $primarySubUrl  = $primarySaleKey ? url('/sub/'.$primarySaleKey->sub_id) : null;
    @endphp

    @if($subscriptions->isNotEmpty())
        @foreach($subscriptions as $subscription)
            @php($saleKey = $saleKeys[$subscription->id] ?? null)
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
                    <span class="sub-row-label">Устройств</span>
                    <span class="sub-row-value">{{ $subscription->devices_count }} / {{ $subscription->max_devices }}</span>
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
                    <a href="{{ route('cabinet.devices') }}" class="btn btn-secondary btn-sm">Управление устройствами и ключами</a>
                    <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Продлить подписку</a>
                </div>
            </div>
        @endforeach
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
                <span class="sub-row-label">Устройств</span>
                <span class="sub-row-value muted">—</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Действует до</span>
                <span class="sub-row-value muted">—</span>
            </div>
            <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 12px;">
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Оформить подписку</a>
            </div>
        </div>
    @endif

    <div class="mt-24">
        @include('partials.platform-instructions', [
            'subUrl' => $primarySubUrl,
            'title'  => 'Как подключиться',
            'desc'   => $primarySubUrl
                ? 'Выберите вашу платформу и следуйте трём шагам. Ссылку подписки можно найти во вкладке «Управление».'
                : 'Выберите вашу платформу и следуйте трём шагам. После оформления подписки ссылка для подключения появится во вкладке «Управление».',
        ])
    </div>
@endsection

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
                    setBanner('alert-info', 'Оплата ещё обрабатывается. Обновите страницу через минуту или откройте «Управление».');
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
