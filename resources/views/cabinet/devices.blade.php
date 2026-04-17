@extends('layouts.cabinet')

@section('title', 'Управление устройствами')

@section('content')
    <h1 class="cab-page-title">Управление устройствами</h1>
    <p class="cab-page-desc">Список привязанных устройств к вашей подписке.</p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($subscription)
        <div class="cab-card">
            <div class="cab-card-header">
                <span class="cab-card-title">Привязанные устройства</span>
                <span class="cab-badge {{ $devices->count() >= $subscription->max_devices ? 'red' : 'green' }}">
                    {{ $devices->count() }} / {{ $subscription->max_devices }}
                </span>
            </div>

            @if($devices->count() > 0)
                <div class="devices-list">
                    @foreach($devices as $device)
                        <div class="device-item">
                            <div class="device-info">
                                <div class="device-icon">
                                    @if(str_contains($device->display_name, 'iPhone') || str_contains($device->display_name, 'iPad'))
                                        📱
                                    @elseif(str_contains($device->display_name, 'Android'))
                                        📱
                                    @elseif(str_contains($device->display_name, 'Mac'))
                                        💻
                                    @elseif(str_contains($device->display_name, 'Windows'))
                                        🖥️
                                    @elseif(str_contains($device->display_name, 'Linux'))
                                        🐧
                                    @else
                                        📟
                                    @endif
                                </div>
                                <div class="device-details">
                                    <div class="device-name">{{ $device->display_name }}</div>
                                    <div class="device-meta">
                                        @if($device->last_active_at)
                                            Последняя активность: {{ $device->last_active_at->diffForHumans() }}
                                        @else
                                            Добавлено: {{ $device->created_at->format('d.m.Y H:i') }}
                                        @endif
                                        @if($device->ip_address)
                                            <span class="device-ip">• IP: {{ $device->ip_address }}</span>
                                        @endif
                                    </div>
                                    <div class="device-hwid">
                                        <span class="hwid-label">HWID:</span>
                                        <code class="hwid-value">{{ Str::limit($device->hwid, 24, '...') }}</code>
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('cabinet.devices.destroy', $device) }}" method="POST" 
                                  onsubmit="return confirm('Вы уверены, что хотите удалить это устройство?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="cab-empty">
                    <p>Нет привязанных устройств.</p>
                    <p class="text-muted">Регистрация HWID: <code>POST {{ url('/api/device/register') }}</code> с заголовком <code>X-Api-Token</code> и телом <code>sub_id</code>, <code>hwid</code> (после настройки <code>API_TOKEN</code> в .env).</p>
                </div>
            @endif
        </div>

        <div class="cab-card mt-24">
            <div class="cab-card-header">
                <span class="cab-card-title">Информация о подписке</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Тариф</span>
                <span class="sub-row-value">{{ $subscription->plan->name }}</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Максимум устройств</span>
                <span class="sub-row-value">{{ $subscription->max_devices }}</span>
            </div>
            <div class="sub-row">
                <span class="sub-row-label">Действует до</span>
                <span class="sub-row-value">{{ $subscription->expires_at->format('d.m.Y') }}</span>
            </div>
        </div>
    @else
        <div class="cab-card">
            <div class="cab-empty">
                <p>У вас нет активной подписки.</p>
                <p class="text-muted">Оформите подписку, чтобы начать использовать VPN.</p>
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm mt-16">Выбрать тариф</a>
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

.devices-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.device-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    gap: 16px;
}

.device-info {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    flex: 1;
    min-width: 0;
}

.device-icon {
    font-size: 1.8rem;
    line-height: 1;
    flex-shrink: 0;
}

.device-details {
    flex: 1;
    min-width: 0;
}

.device-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.device-meta {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 6px;
}

.device-ip {
    margin-left: 4px;
}

.device-hwid {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
}

.hwid-label {
    color: var(--text-muted);
}

.hwid-value {
    background: var(--bg-card);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    color: var(--text-secondary);
}

@media (max-width: 640px) {
    .device-item {
        flex-direction: column;
        align-items: stretch;
    }

    .device-info {
        margin-bottom: 12px;
    }
}
</style>
@endpush
