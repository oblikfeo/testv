@extends('layouts.cabinet')

@section('title', 'Управление')

@section('content')
    <h1 class="cab-page-title">Управление</h1>
    <p class="cab-page-desc">Устройства и ключи подключения по каждой активной подписке.</p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($subscriptions->isNotEmpty())
        @foreach($subscriptions as $subscription)
            @php($devices = $subscription->devices)
            @php($saleKey = $saleKeys[$subscription->id] ?? null)
            <div class="sub-block {{ !$loop->first ? 'mt-24' : '' }}">
                <div class="sub-block-header">
                    <div class="sub-block-title">
                        <h2>{{ $subscription->plan->name }}</h2>
                        @if($subscriptions->count() > 1)
                            <span class="sub-block-num">Подписка #{{ $loop->iteration }}</span>
                        @endif
                    </div>
                    <div class="sub-block-meta">
                        <span class="sub-block-meta-item">
                            <span class="meta-label">До</span>
                            <span class="meta-value">{{ $subscription->expires_at->format('d.m.Y') }}</span>
                        </span>
                        <span class="sub-block-meta-item">
                            <span class="meta-label">Устройств</span>
                            <span class="meta-value">{{ $devices->count() }} / {{ $subscription->max_devices }}</span>
                        </span>
                        @if($subscription->isActive())
                            <span class="cab-badge green">Активна</span>
                        @else
                            <span class="cab-badge gray">Не активна</span>
                        @endif
                    </div>
                </div>

                <div class="manage-grid">
                    <div class="cab-card manage-card">
                        <div class="cab-card-header">
                            <span class="cab-card-title">Устройства</span>
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
                                <p class="text-muted">Подключитесь к VPN — устройство появится здесь автоматически.</p>
                            </div>
                        @endif
                    </div>

                    <div class="cab-card manage-card">
                        <div class="cab-card-header">
                            <span class="cab-card-title">Ключ подключения</span>
                            @if($saleKey)
                                @if($saleKey->is_admin_bundle)
                                    <span class="cab-badge gray">Все серверы</span>
                                @elseif($saleKey->is_sponsor)
                                    <span class="cab-badge gray">2 сервера</span>
                                @endif
                            @endif
                        </div>

                        @if($saleKey)
                            @php($subUrl = url('/sub/'.$saleKey->sub_id))
                            @php($keyId = 'sub-url-' . $subscription->id)
                            <p class="key-desc">Добавьте ссылку в Happ или другой клиент — трафик и срок синхронизируются с сервером.</p>

                            <div class="key-row">
                                <code id="{{ $keyId }}">{{ $subUrl }}</code>
                            </div>

                            <div class="key-actions">
                                <button type="button"
                                        class="btn btn-primary btn-sm"
                                        onclick="avaCopyKey('{{ $keyId }}', this)">
                                    Копировать
                                </button>
                                <a href="happ://add/{{ $subUrl }}" class="btn btn-secondary btn-sm">
                                    Открыть в Happ
                                </a>
                            </div>

                            @if($saleKey->total_bytes > 0)
                                @php($usagePct = $saleKey->getUsagePercent())
                                <div class="key-usage">
                                    <div class="key-usage-label">
                                        <span>Использовано трафика</span>
                                        <span class="key-usage-value">{{ $usagePct }}%</span>
                                    </div>
                                    <div class="key-usage-bar">
                                        <div class="key-usage-bar-fill" style="width: {{ $usagePct }}%"></div>
                                    </div>
                                </div>
                            @endif

                            @if($saleKey->expires_at)
                                <div class="key-expires">
                                    <span class="meta-label">Действует до</span>
                                    <span class="meta-value">{{ $saleKey->expires_at->format('d.m.Y') }}</span>
                                </div>
                            @endif
                        @else
                            <div class="cab-empty">
                                <p>Ключ ещё не выпущен.</p>
                                <p class="text-muted">Обратитесь в поддержку, если ключ не появляется.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="cab-card">
            <div class="cab-empty">
                <p>У вас нет активных подписок.</p>
                <p class="text-muted">Оформите подписку, чтобы начать использовать VPN.</p>
                <a href="{{ route('cabinet.history') }}" class="btn btn-primary btn-sm mt-16">Выбрать тариф</a>
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
.cab-main {
    max-width: 1080px;
}

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

.sub-block-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    padding: 0 4px;
}

.sub-block-title h2 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.sub-block-num {
    display: inline-block;
    margin-top: 2px;
    font-size: 0.75rem;
    color: var(--text-muted);
    letter-spacing: 0.3px;
}

.sub-block-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.sub-block-meta-item {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.meta-label {
    font-size: 0.72rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.meta-value {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: 2px;
}

.manage-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    align-items: start;
}

.manage-card {
    padding: 22px;
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
    padding: 14px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    gap: 12px;
}

.device-info {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.device-icon {
    font-size: 1.5rem;
    line-height: 1;
    flex-shrink: 0;
}

.device-details {
    flex: 1;
    min-width: 0;
}

.device-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-primary);
    margin-bottom: 4px;
    word-break: break-word;
}

.device-meta {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 6px;
}

.device-ip { margin-left: 4px; }

.device-hwid {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.7rem;
    flex-wrap: wrap;
}

.hwid-label { color: var(--text-muted); }

.hwid-value {
    background: var(--bg-card);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    color: var(--text-secondary);
    word-break: break-all;
}

.key-desc {
    font-size: 0.82rem;
    color: var(--text-secondary);
    line-height: 1.5;
    margin-bottom: 12px;
}

.key-row {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 12px 14px;
    margin: 0 0 12px;
}

.key-row code {
    display: block;
    font-size: 0.78rem;
    color: var(--text-secondary);
    font-family: 'Consolas', 'Courier New', monospace;
    word-break: break-all;
    line-height: 1.5;
}

.key-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 14px;
}

.key-actions .btn {
    flex: 1;
    min-width: 130px;
    text-decoration: none;
}

.key-usage {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border-color);
}

.key-usage-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-bottom: 6px;
}

.key-usage-value {
    color: var(--text-primary);
    font-weight: 600;
}

.key-usage-bar {
    width: 100%;
    height: 6px;
    background: var(--bg-primary);
    border-radius: 100px;
    overflow: hidden;
}

.key-usage-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--red-primary), var(--red-light));
    transition: width 0.3s ease;
}

.key-expires {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--border-color);
    font-size: 0.82rem;
}

@media (max-width: 960px) {
    .manage-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .sub-block-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .sub-block-meta {
        width: 100%;
        justify-content: space-between;
    }

    .sub-block-meta-item {
        align-items: flex-start;
    }

    .device-item {
        flex-direction: column;
        align-items: stretch;
    }

    .device-info { margin-bottom: 10px; }

    .key-actions .btn { flex: 1 1 100%; }
}
</style>
@endpush

@push('scripts')
<script>
function avaCopyKey(elementId, btn) {
    var el = document.getElementById(elementId);
    if (!el) return;
    var text = el.innerText.trim();
    var fallback = function () {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).catch(fallback);
    } else {
        fallback();
    }
    if (btn) {
        var original = btn.innerText;
        btn.innerText = 'Скопировано';
        btn.disabled = true;
        setTimeout(function () {
            btn.innerText = original;
            btn.disabled = false;
        }, 1500);
    }
}
</script>
@endpush
