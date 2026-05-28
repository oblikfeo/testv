@if(!empty($connectionUri))
    <div class="cab-card {{ $wrapperClass ?? 'mt-24' }}">
        <div class="cab-card-header">
            <span class="cab-card-title">Подписочная ссылка</span>
        </div>
        <p class="cab-page-desc" style="margin-bottom: 12px;">Добавьте в Happ / Hiddify / v2RayTun как подписку — в списке появятся два сервера (Hysteria2 и VLESS).</p>
        <div style="display: flex; gap: 10px; align-items: stretch; flex-wrap: wrap;">
            <input type="text" id="{{ $inputId ?? 'connectionUriInput' }}" readonly value="{{ $connectionUri }}"
                   style="flex:1;min-width:200px;font-family:monospace;font-size:0.8rem;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,0.15);background:rgba(0,0,0,0.25);color:inherit;">
            <button type="button" class="btn btn-primary btn-sm cabinet-copy-sub-btn" data-copy-target="{{ $inputId ?? 'connectionUriInput' }}">Копировать</button>
        </div>
    </div>
@endif
