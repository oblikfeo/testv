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

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Тарифы</span>
        </div>

        <div class="pricing-table-wrap">
            <table class="pricing-table">
                <thead>
                    <tr>
                        <th>Период</th>
                        <th>
                            <div class="plan-name">Стандартный</div>
                            <div class="plan-desc">2 устройства</div>
                        </th>
                        <th class="plan-highlight">
                            <span class="plan-badge">выгодно</span>
                            <div class="plan-name">Расширенный</div>
                            <div class="plan-desc">5 устройств</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $standardPlans = $plans->where('devices', 2);
                        $extendedPlans = $plans->where('devices', 5);
                        $periods = [30 => '30 дней', 90 => '90 дней', 180 => '180 дней'];
                    @endphp
                    
                    @foreach($periods as $days => $label)
                        @php
                            $standard = $standardPlans->where('days', $days)->first();
                            $extended = $extendedPlans->where('days', $days)->first();
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td>
                                @if($standard)
                                    <div class="plan-price-row">
                                        <strong>{{ $standard->formatted_price }}</strong>
                                        @if($standard->discount > 0)
                                            <span class="table-badge">−{{ $standard->discount }}%</span>
                                        @endif
                                    </div>
                                    <form action="{{ route('payment.create') }}" method="POST" class="plan-buy-form">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $standard->id }}">
                                        <button type="submit" class="btn btn-secondary btn-xs">Купить</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="plan-highlight">
                                @if($extended)
                                    <div class="plan-price-row">
                                        <strong>{{ $extended->formatted_price }}</strong>
                                        @if($extended->discount > 0)
                                            <span class="table-badge">−{{ $extended->discount }}%</span>
                                        @endif
                                    </div>
                                    <form action="{{ route('payment.create') }}" method="POST" class="plan-buy-form">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $extended->id }}">
                                        <button type="submit" class="btn btn-primary btn-xs">Купить</button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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

.pricing-table-wrap {
    overflow-x: auto;
    margin: 0 -28px -28px;
    padding: 0 28px 28px;
}

.pricing-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 500px;
}

.pricing-table th,
.pricing-table td {
    padding: 16px 12px;
    text-align: center;
    font-size: 0.9rem;
    border-bottom: 1px solid var(--border-color);
}

.pricing-table th {
    background: var(--bg-primary);
    font-weight: 500;
    color: var(--text-secondary);
}

.pricing-table th:first-child {
    text-align: left;
    padding-left: 0;
}

.pricing-table td:first-child {
    text-align: left;
    padding-left: 0;
    font-weight: 500;
    color: var(--text-secondary);
}

.pricing-table tbody tr:last-child td {
    border-bottom: none;
}

.plan-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--text-primary);
}

.plan-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 2px;
}

.plan-highlight {
    background: rgba(220, 38, 38, 0.03);
    position: relative;
}

.plan-badge {
    display: inline-block;
    background: var(--red-primary);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 100px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.table-badge {
    display: inline-block;
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 6px;
}

.plan-price-row {
    margin-bottom: 8px;
}

.plan-price-row strong {
    font-size: 1rem;
    color: var(--text-primary);
}

.plan-buy-form {
    margin: 0;
}

.btn-xs {
    padding: 6px 14px;
    font-size: 0.75rem;
}

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
