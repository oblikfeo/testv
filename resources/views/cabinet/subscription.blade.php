@extends('layouts.cabinet')

@section('title', 'Подписка')

@section('content')
    <h1 class="cab-page-title">Подписка</h1>
    <p class="cab-page-desc">Статус вашего текущего тарифа и ключ подключения.</p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Текущий тариф</span>
            @if($subscription && $subscription->isActive())
                <span class="cab-badge green">Активна</span>
            @elseif($subscription && $subscription->isExpired())
                <span class="cab-badge red">Истекла</span>
            @else
                <span class="cab-badge gray">Не активна</span>
            @endif
        </div>

        @if($subscription)
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
                <a href="{{ route('cabinet.devices') }}" class="btn btn-secondary btn-sm">Управление устройствами</a>
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Продлить подписку</a>
            </div>
        @else
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
            <div class="sub-row">
                <span class="sub-row-label">Ключ подключения</span>
                <span class="sub-row-value muted">—</span>
            </div>
            <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 12px;">
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm">Оформить подписку</a>
                <a href="{{ route('keys.index') }}" class="btn btn-secondary btn-sm">Мои ключи</a>
            </div>
        @endif
    </div>

    @if($subscription && $subscription->isActive())
        <div class="cab-card mt-24">
            <div class="cab-card-header">
                <span class="cab-card-title">Быстрые действия</span>
            </div>
            <div class="quick-actions">
                <a href="{{ route('cabinet.devices') }}" class="quick-action">
                    <span class="quick-action-icon">📱</span>
                    <span class="quick-action-text">
                        <strong>Устройства</strong>
                        <small>{{ $subscription->devices_count }} из {{ $subscription->max_devices }}</small>
                    </span>
                </a>
                <a href="{{ route('keys.index') }}" class="quick-action">
                    <span class="quick-action-icon">🔑</span>
                    <span class="quick-action-text">
                        <strong>Мои ключи</strong>
                        <small>Конфигурации VPN</small>
                    </span>
                </a>
            </div>
        </div>
    @endif
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

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.quick-action {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    text-decoration: none;
    color: inherit;
    transition: all var(--transition);
}

.quick-action:hover {
    border-color: var(--red-primary);
    background: rgba(220, 38, 38, 0.03);
}

.quick-action-icon {
    font-size: 1.6rem;
}

.quick-action-text {
    display: flex;
    flex-direction: column;
}

.quick-action-text strong {
    font-size: 0.9rem;
    color: var(--text-primary);
}

.quick-action-text small {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 2px;
}
</style>
@endpush
