@extends('layouts.cabinet')

@section('title', 'Покупки')

@section('content')
    <h1 class="cab-page-title">Покупки</h1>
    <p class="cab-page-desc">Выберите тариф и оформите подписку.</p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @php($purchaseChoice = session('purchase_choice'))
    @if($purchaseChoice)
        <div class="alert alert-choice">
            <p class="choice-title">
                Найдены подходящие подписки для тарифа
                <strong>{{ $purchaseChoice['plan']['name'] }} ({{ $purchaseChoice['plan']['period_label'] }}, {{ $purchaseChoice['plan']['devices'] }} устр.)</strong>.
            </p>
            <p class="choice-subtitle">Выберите действие:</p>
            <div class="choice-actions">
                <form action="{{ route('payment.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $purchaseChoice['plan']['id'] }}">
                    <input type="hidden" name="purchase_action" value="new_purchase">
                    <button type="submit" class="tariff-btn tariff-btn--primary">Купить новую ({{ $purchaseChoice['plan']['price'] }})</button>
                </form>
                @foreach($purchaseChoice['subscriptions'] as $sub)
                    <form action="{{ route('payment.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $purchaseChoice['plan']['id'] }}">
                        <input type="hidden" name="purchase_action" value="renew_subscription">
                        <input type="hidden" name="target_subscription_id" value="{{ $sub['id'] }}">
                        <button type="submit" class="tariff-btn">
                            Продлить #{{ $sub['id'] }} ({{ $sub['plan_name'] }}, до {{ $sub['expires_at'] ?? '—' }})
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    <div class="tariffs-section">
        <div class="tariffs-header">
            <h2 class="tariffs-title">Выберите тариф</h2>
        </div>

        <div class="tariffs-grid">
            <div class="tariff-card">
                <div class="tariff-card-head">
                    <span class="tariff-name">Стандартный</span>
                    <span class="tariff-devices">2 устройства</span>
                </div>
                <div class="tariff-options">
                    @foreach($standardPlans as $plan)
                        <div class="tariff-option">
                            <div class="tariff-option-info">
                                <span class="tariff-period">{{ $plan->period_label }}</span>
                                <span class="tariff-price">{{ $plan->formatted_price }}</span>
                                @if($plan->discount > 0)
                                    <span class="tariff-discount">−{{ $plan->discount }}%</span>
                                @endif
                            </div>
                            <div class="tariff-actions">
                                <form action="{{ route('payment.create') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="tariff-btn">Купить</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tariff-card tariff-card--popular">
                <div class="tariff-popular-tag">Выгодно</div>
                <div class="tariff-card-head">
                    <span class="tariff-name">Расширенный</span>
                    <span class="tariff-devices">5 устройств</span>
                </div>
                <div class="tariff-options">
                    @foreach($extendedPlans as $plan)
                        <div class="tariff-option">
                            <div class="tariff-option-info">
                                <span class="tariff-period">{{ $plan->period_label }}</span>
                                <span class="tariff-price">{{ $plan->formatted_price }}</span>
                                @if($plan->discount > 0)
                                    <span class="tariff-discount">−{{ $plan->discount }}%</span>
                                @endif
                            </div>
                            <div class="tariff-actions">
                                <form action="{{ route('payment.create') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="tariff-btn tariff-btn--primary">Купить</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="cab-card mt-24">
        <div class="cab-card-header">
            <span class="cab-card-title">История покупок</span>
        </div>

        @if($orders->count() > 0)
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Тариф</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($order->plan)
                                    {{ $order->plan->name }} ({{ $order->plan->period_label }})
                                @else
                                    {{ $order->note ?? '—' }}
                                @endif
                            </td>
                            <td class="amount">
                                @if($order->amount)
                                    {{ number_format($order->amount, 0, '', ' ') }} ₽
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @switch($order->status->value ?? $order->status)
                                    @case('pending')
                                        <span class="status-pending">Ожидает оплаты</span>
                                        @break
                                    @case('fulfilled')
                                        <span class="status-paid">Оплачен</span>
                                        @break
                                    @case('cancelled')
                                        <span class="status-cancelled">Отменён</span>
                                        @break
                                    @default
                                        <span class="status-unknown">{{ $order->status }}</span>
                                @endswitch
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($orders->hasPages())
                <div class="pagination-wrap">
                    {{ $orders->links() }}
                </div>
            @endif
        @else
            <div class="cab-empty">
                <p>Покупок пока нет.</p>
            </div>
        @endif
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

.alert-error {
    background: rgba(220, 38, 38, 0.1);
    border: 1px solid rgba(220, 38, 38, 0.2);
    color: var(--red-light);
}

/* Tariffs Section */
.tariffs-section {
    margin-bottom: 24px;
}

.tariffs-header {
    margin-bottom: 20px;
}

.tariffs-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.tariffs-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

@media (max-width: 640px) {
    .tariffs-grid {
        grid-template-columns: 1fr;
    }
}

.tariff-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    position: relative;
}

.tariff-card--popular {
    border-color: var(--red-primary);
}

.tariff-popular-tag {
    position: absolute;
    top: 12px;
    right: 12px;
    background: var(--red-primary);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 100px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tariff-card-head {
    padding: 20px 20px 16px;
    border-bottom: 1px solid var(--border-color);
}

.tariff-name {
    display: block;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.tariff-devices {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.tariff-options {
    padding: 8px 0;
}

.tariff-option {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 190px;
    align-items: center;
    padding: 12px 20px;
    transition: background var(--transition);
    gap: 12px;
}

.tariff-option:hover {
    background: rgba(255, 255, 255, 0.02);
}

.tariff-option-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex-wrap: wrap;
}

.tariff-period {
    font-size: 0.85rem;
    color: var(--text-secondary);
    min-width: 70px;
}

.tariff-price {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
}

.tariff-discount {
    display: inline-block;
    background: rgba(34, 197, 94, 0.12);
    color: #22c55e;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 7px;
    border-radius: 4px;
}

.tariff-btn {
    padding: 7px 16px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition);
    font-family: var(--font-main);
}

.tariff-btn:hover {
    border-color: var(--text-muted);
    color: var(--text-primary);
}

.tariff-actions .tariff-btn {
    width: 100%;
}

@media (max-width: 900px) {
    .tariff-option {
        grid-template-columns: 1fr;
        align-items: stretch;
    }
}

.alert-choice {
    background: rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(59, 130, 246, 0.25);
    color: var(--text-secondary);
}

.choice-title {
    color: var(--text-primary);
    margin-bottom: 6px;
}

.choice-subtitle {
    color: var(--text-muted);
    margin-bottom: 12px;
    font-size: 0.85rem;
}

.choice-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tariff-btn--primary {
    background: var(--red-primary);
    border-color: var(--red-primary);
    color: #fff;
}

.tariff-btn--primary:hover {
    background: var(--red-hover);
    border-color: var(--red-hover);
    color: #fff;
}

/* History Table */
.status-pending {
    color: #f59e0b;
}

.status-paid {
    color: #22c55e;
    font-weight: 500;
}

.status-cancelled {
    color: var(--text-muted);
}

.pagination-wrap {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}
</style>
@endpush
