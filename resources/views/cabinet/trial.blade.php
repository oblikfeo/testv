@extends('layouts.cabinet')

@section('title', 'Тест-драйв')

@push('scripts')
<script>
    function generateTrial(btn) {
        btn.disabled = true;
        btn.textContent = 'Генерация...';
        setTimeout(function() {
            var key = 'vless://test-' + Math.random().toString(36).substring(2, 10) + '@ava-vpn.net:443';
            document.getElementById('trialKeyValue').textContent = key;
            document.getElementById('trialKey').style.display = 'block';
            btn.textContent = 'Ключ выдан';
            btn.closest('.cab-card').querySelector('.cab-badge').textContent = 'Использовано';
            btn.closest('.cab-card').querySelector('.cab-badge').className = 'cab-badge gray';
        }, 800);
    }

    function copyKey() {
        var key = document.getElementById('trialKeyValue').textContent;
        navigator.clipboard.writeText(key);
        var copyBtn = document.querySelector('#trialKey .btn');
        copyBtn.textContent = 'Скопировано!';
        setTimeout(function() { copyBtn.textContent = 'Копировать'; }, 1500);
    }
</script>
@endpush

@section('content')
    <h1 class="cab-page-title">Тест-драйв</h1>
    <p class="cab-page-desc">Бесплатный ключ на 8 часов, чтобы проверить сервис перед покупкой. Выдаётся один раз на аккаунт.</p>

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Тестовый ключ</span>
            <span class="cab-badge red">Доступно</span>
        </div>
        <p style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px;">
            Нажмите кнопку — мы сгенерируем временный ключ подключения. Скопируйте его в приложение и пользуйтесь 8 часов бесплатно.
        </p>
        <button class="btn btn-primary btn-sm" onclick="generateTrial(this)">Получить ключ</button>
        <div class="key-block" id="trialKey" style="display: none;">
            <div class="key-row">
                <code id="trialKeyValue"></code>
                <button class="btn btn-secondary btn-sm" onclick="copyKey()">Копировать</button>
            </div>
        </div>
    </div>
@endsection
