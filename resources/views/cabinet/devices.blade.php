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
            <section class="sub-block {{ !$loop->first ? 'mt-24' : '' }}">
                <header class="sub-block-header">
                    <div class="sub-block-title">
                        @if($subscriptions->count() > 1)
                            <span class="sub-block-num">Подписка #{{ $loop->iteration }}</span>
                        @endif
                        <h2>{{ $subscription->plan->name }}</h2>
                    </div>
                    <div class="sub-block-meta">
                        <div class="sub-block-meta-item">
                            <span class="meta-label">Действует до</span>
                            <span class="meta-value">{{ $subscription->expires_at->format('d.m.Y') }}</span>
                        </div>
                        <div class="sub-block-meta-divider"></div>
                        <div class="sub-block-meta-item">
                            <span class="meta-label">Устройств</span>
                            <span class="meta-value">{{ $devices->count() }} / {{ $subscription->max_devices }}</span>
                        </div>
                        @if($subscription->isActive())
                            <span class="cab-badge green">Активна</span>
                        @else
                            <span class="cab-badge gray">Не активна</span>
                        @endif
                    </div>
                </header>

                <div class="manage-grid">
                    <article class="cab-card manage-card">
                        <div class="cab-card-header">
                            <span class="cab-card-title">Устройства</span>
                            <span class="cab-badge {{ $devices->count() >= $subscription->max_devices ? 'red' : 'gray' }}">
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
                            <div class="manage-empty">
                                <div class="manage-empty-icon">📱</div>
                                <p class="manage-empty-title">Нет привязанных устройств</p>
                                <p class="manage-empty-hint">Подключитесь к VPN — устройство появится здесь автоматически.</p>
                            </div>
                        @endif
                    </article>

                    <article class="cab-card manage-card">
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
                                    <span class="btn-ico">⧉</span>
                                    <span>Копировать</span>
                                </button>
                                <a href="happ://add/{{ $subUrl }}" class="btn btn-secondary btn-sm">
                                    <span class="btn-ico">↗</span>
                                    <span>Открыть в Happ</span>
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
                        @else
                            <div class="manage-empty">
                                <div class="manage-empty-icon">🔑</div>
                                <p class="manage-empty-title">Ключ ещё не выпущен</p>
                                <p class="manage-empty-hint">Обратитесь в поддержку, если ключ не появляется.</p>
                            </div>
                        @endif
                    </article>
                </div>
            </section>
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

.sub-block {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 22px 22px 20px;
}

.sub-block-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    padding: 0 2px 18px;
    margin-bottom: 18px;
    border-bottom: 1px solid var(--border-color);
}

.sub-block-title {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sub-block-num {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 500;
}

.sub-block-title h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.2;
}

.sub-block-meta {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}

.sub-block-meta-item {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
    gap: 4px;
}

.sub-block-meta-divider {
    width: 1px;
    height: 28px;
    background: var(--border-color);
}

.meta-label {
    font-size: 0.68rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 500;
}

.meta-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--text-primary);
}

.manage-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    align-items: stretch;
}

.manage-card {
    padding: 20px;
    display: flex;
    flex-direction: column;
    background: var(--bg-primary);
    border-color: var(--border-color);
}

.manage-card .cab-card-header {
    margin-bottom: 16px;
}

.devices-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.device-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: var(--bg-card);
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
    font-size: 1.4rem;
    line-height: 1;
    flex-shrink: 0;
    padding-top: 2px;
}

.device-details {
    flex: 1;
    min-width: 0;
}

.device-name {
    font-weight: 600;
    font-size: 0.88rem;
    color: var(--text-primary);
    margin-bottom: 3px;
    word-break: break-word;
}

.device-meta {
    font-size: 0.73rem;
    color: var(--text-muted);
    margin-bottom: 5px;
    line-height: 1.45;
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
    background: var(--bg-primary);
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
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 11px 14px;
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
    margin-bottom: 0;
}

.key-actions .btn {
    flex: 1;
    min-width: 130px;
    text-decoration: none;
    padding: 10px 16px;
    font-size: 0.82rem;
}

.btn-ico {
    font-size: 0.95rem;
    line-height: 1;
}

.key-usage {
    margin-top: 16px;
    padding-top: 14px;
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
    background: var(--bg-card);
    border-radius: 100px;
    overflow: hidden;
}

.key-usage-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--red-primary), var(--red-light));
    transition: width 0.3s ease;
}

.manage-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 28px 16px;
    flex: 1;
    min-height: 160px;
}

.manage-empty-icon {
    font-size: 1.8rem;
    opacity: 0.5;
    margin-bottom: 12px;
    line-height: 1;
}

.manage-empty-title {
    font-size: 0.92rem;
    color: var(--text-primary);
    font-weight: 600;
    margin-bottom: 6px;
}

.manage-empty-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    line-height: 1.45;
    max-width: 280px;
}

@media (max-width: 960px) {
    .manage-grid {
        grid-template-columns: 1fr;
    }

    .sub-block-meta {
        gap: 14px;
    }
}

@media (max-width: 640px) {
    .sub-block {
        padding: 18px 16px;
    }

    .sub-block-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .sub-block-meta {
        width: 100%;
        justify-content: flex-start;
        gap: 16px;
    }

    .sub-block-meta-item {
        align-items: flex-start;
    }

    .sub-block-meta-divider {
        display: none;
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
        var original = btn.querySelector('span:last-child') ? btn.querySelector('span:last-child').innerText : btn.innerText;
        var label = btn.querySelector('span:last-child') || btn;
        label.innerText = 'Скопировано';
        btn.disabled = true;
        setTimeout(function () {
            label.innerText = original;
            btn.disabled = false;
        }, 1500);
    }
}
</script>
@endpush
