@extends('layouts.cabinet')

@section('title', 'Покупки')

@section('content')
<div class="shop-page">
    <header class="shop-hero">
        <h1 class="cab-page-title">Покупки</h1>
        <p class="cab-page-desc">Выберите тариф, оплатите онлайн — подписка активируется автоматически.</p>
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

    <div class="shop-stack">
        <section class="shop-section" aria-labelledby="shop-tiers-title">
            <div class="shop-section-head">
                <h2 id="shop-tiers-title" class="shop-section-title">Тарифы</h2>
                <p class="shop-section-desc">Скидка растёт с длительностью периода</p>
            </div>

            <div class="shop-tiers">
                @include('partials.cabinet-tariff-tier', [
                    'tierName' => 'Стандартный',
                    'devices' => 2,
                    'plans' => $standardPlans,
                    'featured' => false,
                ])
                @include('partials.cabinet-tariff-tier', [
                    'tierName' => 'Расширенный',
                    'devices' => 5,
                    'plans' => $extendedPlans,
                    'featured' => true,
                ])
            </div>
        </section>

        <section class="shop-section cab-card shop-history-card" aria-labelledby="shop-history-title">
            <div class="shop-section-head shop-section-head--row">
                <div>
                    <h2 id="shop-history-title" class="shop-section-title">История покупок</h2>
                    <p class="shop-section-desc">Последние заказы и статусы оплаты</p>
                </div>
                @if($orders->total() > 0)
                    <span class="shop-history-count">{{ $orders->total() }}</span>
                @endif
            </div>

            @if($orders->count() > 0)
                <div class="shop-history-list">
                    @foreach($orders as $order)
                        @php
                            $status = $order->status->value ?? $order->status;
                        @endphp
                        <article class="shop-order">
                            <div class="shop-order-main">
                                <div class="shop-order-plan">
                                    @if($order->plan)
                                        <span class="shop-order-name">{{ $order->plan->name }}</span>
                                        <span class="shop-order-meta">{{ $order->plan->period_label }} · {{ $order->plan->devices }} устр.</span>
                                    @else
                                        <span class="shop-order-name">{{ $order->note ?? 'Заказ' }}</span>
                                    @endif
                                </div>
                                <div class="shop-order-side">
                                    <span class="shop-order-amount">
                                        @if($order->amount)
                                            {{ number_format($order->amount, 0, '', ' ') }} ₽
                                        @else
                                            —
                                        @endif
                                    </span>
                                    <time class="shop-order-date" datetime="{{ $order->created_at->toIso8601String() }}">
                                        {{ $order->created_at->format('d.m.Y H:i') }}
                                    </time>
                                </div>
                            </div>
                            @switch($status)
                                @case('pending')
                                    <span class="shop-order-status is-pending">Ожидает оплаты</span>
                                    @break
                                @case('fulfilled')
                                    <span class="shop-order-status is-paid">Оплачен</span>
                                    @break
                                @case('cancelled')
                                    <span class="shop-order-status is-cancelled">Отменён</span>
                                    @break
                                @default
                                    <span class="shop-order-status is-unknown">{{ $status }}</span>
                            @endswitch
                        </article>
                    @endforeach
                </div>

                @if($orders->hasPages())
                    <div class="shop-pagination">
                        {{ $orders->links() }}
                    </div>
                @endif
            @else
                <div class="shop-history-empty">
                    <p>Покупок пока нет — выберите тариф выше.</p>
                </div>
            @endif
        </section>
    </div>

    @php($purchaseChoice = session('purchase_choice'))

    @if($purchaseChoice)
        <div id="purchase-choice-modal" class="shop-modal is-open">
            <div class="shop-modal-backdrop" data-close-purchase-modal tabindex="-1"></div>
            <div class="shop-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="purchase-choice-title">
                <button type="button" class="shop-modal-close" data-close-purchase-modal aria-label="Закрыть">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </button>
                <h3 id="purchase-choice-title" class="shop-modal-title">Продлить или купить новую?</h3>
                <p class="shop-modal-desc">
                    Тариф <strong>{{ $purchaseChoice['plan']['name'] }}</strong>
                    ({{ $purchaseChoice['plan']['period_label'] }}, {{ $purchaseChoice['plan']['devices'] }} устр.)
                    — {{ $purchaseChoice['plan']['price'] }}
                </p>
                <div class="shop-modal-actions">
                    <form action="{{ route('payment.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $purchaseChoice['plan']['id'] }}">
                        <input type="hidden" name="purchase_action" value="new_purchase">
                        <button type="submit" class="btn btn-primary shop-modal-btn">Купить новую подписку</button>
                    </form>
                    @foreach($purchaseChoice['subscriptions'] as $sub)
                        <form action="{{ route('payment.create') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $purchaseChoice['plan']['id'] }}">
                            <input type="hidden" name="purchase_action" value="renew_subscription">
                            <input type="hidden" name="target_subscription_id" value="{{ $sub['id'] }}">
                            <button type="submit" class="btn btn-secondary shop-modal-btn">
                                Продлить «{{ $sub['plan_name'] }}» до {{ $sub['expires_at'] ?? '—' }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if(session('purchase_choice'))
<script>
(function () {
    const modal = document.getElementById('purchase-choice-modal');
    if (!modal) return;

    const close = function () {
        modal.classList.remove('is-open');
    };

    modal.querySelectorAll('[data-close-purchase-modal]').forEach(function (el) {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            close();
        }
    });
})();
</script>
@endif
@endpush
