@if(!empty($connectionUri))
    @php
        $inputId = $inputId ?? 'connectionUriInput';
    @endphp
    <div class="sub-link">
        <div class="sub-link-header">
            <span class="sub-link-label">Подписочная ссылка</span>
            <span class="sub-link-apps">Happ · v2RayTun</span>
        </div>
        <div class="sub-link-field">
            <input
                type="text"
                id="{{ $inputId }}"
                readonly
                value="{{ $connectionUri }}"
                class="sub-link-input"
                aria-label="Подписочная ссылка"
            >
            <button
                type="button"
                class="sub-link-copy cabinet-copy-sub-btn"
                data-copy-target="{{ $inputId }}"
                aria-label="Копировать ссылку"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <rect x="9" y="9" width="11" height="11" rx="2" stroke="currentColor" stroke-width="1.75"/>
                    <path d="M6 15H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.75"/>
                </svg>
                <span class="sub-link-copy-text">Копировать</span>
            </button>
        </div>
        <p class="sub-link-hint">Добавьте как подписку в приложении — в списке появятся два сервера: Hysteria2 и VLESS.</p>
    </div>
@endif
