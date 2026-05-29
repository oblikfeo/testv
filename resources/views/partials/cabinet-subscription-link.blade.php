@if(!empty($connectionUri))
    <div class="sub-link">
        <span class="sub-link-label">Подписочная ссылка</span>
        <div class="sub-link-row">
            <input type="text" id="{{ $inputId ?? 'connectionUriInput' }}" readonly value="{{ $connectionUri }}" class="sub-link-input">
            <button type="button" class="btn btn-primary btn-sm cabinet-copy-sub-btn" data-copy-target="{{ $inputId ?? 'connectionUriInput' }}">Копировать</button>
        </div>
        <p class="sub-link-hint">Добавьте как подписку в Happ или v2RayTun — в списке появятся два сервера (Hysteria2 и VLESS).</p>
    </div>
@endif
