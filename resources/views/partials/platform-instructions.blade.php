{{--
    Блок «Как подключиться» с вкладками iOS / Android / Windows / macOS.
    Аргументы (опционально):
      $subUrl   — ссылка подписки для кнопок «Добавить в Happ / v2RayTun»
      $title    — заголовок блока (по умолчанию «Как подключиться»)
      $desc     — короткое описание под заголовком
--}}
@php
    $piSubUrl = $subUrl ?? null;
    $piTitle  = $title  ?? 'Как подключиться';
    $piDesc   = $desc   ?? 'Выберите вашу платформу и следуйте трём шагам.';
    // Уникальный id, чтобы можно было подключить блок несколько раз на странице
    static $piCounter = 0;
    $piCounter++;
    $piId = 'pi-' . $piCounter;
@endphp

<section class="cab-card platform-block" id="{{ $piId }}" data-sub-url="{{ $piSubUrl ? e($piSubUrl) : '' }}">
    <div class="platform-block-head">
        <h2 class="platform-block-title">{{ $piTitle }}</h2>
        <p class="platform-block-desc">{{ $piDesc }}</p>
    </div>

    <div class="platform-tabs" role="tablist">
        <button type="button" class="platform-tab active" data-platform="ios" role="tab">iOS</button>
        <button type="button" class="platform-tab" data-platform="android" role="tab">Android</button>
        <button type="button" class="platform-tab" data-platform="windows" role="tab">Windows</button>
        <button type="button" class="platform-tab" data-platform="macos" role="tab">macOS</button>
    </div>

    {{-- iOS --}}
    <div class="platform-instructions active" data-platform="ios">
        <div class="instruction-card">
            <div class="step-badge">1</div>
            <div class="step-content">
                <h3>Установите приложение</h3>
                <p>Скачайте Happ или v2RayTun из App Store.</p>
                <div class="inline-buttons">
                    <a href="https://apps.apple.com/app/happ-proxy-utility/id6504287215" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Happ — App Store</a>
                    <a href="https://apps.apple.com/app/v2raytun/id6476628951" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">v2RayTun — App Store</a>
                </div>
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">2</div>
            <div class="step-content">
                <h3>Добавьте подписку</h3>
                <p>Откройте приложение, нажмите «+» и вставьте скопированную ссылку подключения. Или нажмите кнопку ниже для автоматического добавления.</p>
                @if($piSubUrl)
                    <div class="inline-buttons">
                        <button type="button" class="btn btn-secondary btn-sm" data-import="happ">+ Добавить в Happ</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-import="v2raytun">+ Добавить в v2RayTun</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-copy>Копировать ссылку</button>
                    </div>
                @endif
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">3</div>
            <div class="step-content">
                <h3>Подключитесь</h3>
                <p>На главном экране приложения включите VPN. При необходимости выберите сервер из списка. Разрешите создание VPN-профиля при первом запуске.</p>
            </div>
        </div>
    </div>

    {{-- Android --}}
    <div class="platform-instructions" data-platform="android">
        <div class="instruction-card">
            <div class="step-badge">1</div>
            <div class="step-content">
                <h3>Установите приложение</h3>
                <p>Скачайте Happ или v2RayTun из Google Play.</p>
                <div class="inline-buttons">
                    <a href="https://play.google.com/store/apps/details?id=com.happproxy" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Happ — Google Play</a>
                    <a href="https://play.google.com/store/apps/details?id=com.v2raytun.android" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">v2RayTun — Google Play</a>
                </div>
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">2</div>
            <div class="step-content">
                <h3>Добавьте подписку</h3>
                <p>Откройте приложение, нажмите «+» и вставьте скопированную ссылку подключения.</p>
                @if($piSubUrl)
                    <div class="inline-buttons">
                        <button type="button" class="btn btn-secondary btn-sm" data-import="happ">+ Добавить в Happ</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-import="v2raytun">+ Добавить в v2RayTun</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-copy>Копировать ссылку</button>
                    </div>
                @endif
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">3</div>
            <div class="step-content">
                <h3>Подключитесь</h3>
                <p>Нажмите кнопку подключения на главном экране. При первом запуске разрешите приложению создать VPN-соединение.</p>
            </div>
        </div>
    </div>

    {{-- Windows --}}
    <div class="platform-instructions" data-platform="windows">
        <div class="instruction-card">
            <div class="step-badge">1</div>
            <div class="step-content">
                <h3>Скачайте приложение</h3>
                <p>Загрузите Happ или v2RayTun для Windows.</p>
                <div class="inline-buttons">
                    <a href="https://apps.microsoft.com/detail/9nwf2wpgc3sq" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Happ — Microsoft Store</a>
                    <a href="https://github.com/mdf45/v2raytun/releases" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">v2RayTun — Скачать</a>
                </div>
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">2</div>
            <div class="step-content">
                <h3>Добавьте подписку</h3>
                <p>Запустите приложение, добавьте новую подписку и вставьте ссылку подключения.</p>
                @if($piSubUrl)
                    <div class="inline-buttons">
                        <button type="button" class="btn btn-secondary btn-sm" data-copy>Копировать ссылку</button>
                    </div>
                @endif
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">3</div>
            <div class="step-content">
                <h3>Подключитесь</h3>
                <p>Выберите сервер из списка и нажмите кнопку подключения.</p>
            </div>
        </div>
    </div>

    {{-- macOS --}}
    <div class="platform-instructions" data-platform="macos">
        <div class="instruction-card">
            <div class="step-badge">1</div>
            <div class="step-content">
                <h3>Установите приложение</h3>
                <p>Скачайте Happ или v2RayTun из App Store для Mac.</p>
                <div class="inline-buttons">
                    <a href="https://apps.apple.com/app/happ-proxy-utility/id6504287215" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Happ — App Store</a>
                    <a href="https://apps.apple.com/app/v2raytun/id6476628951" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">v2RayTun — App Store</a>
                </div>
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">2</div>
            <div class="step-content">
                <h3>Добавьте подписку</h3>
                <p>Откройте приложение, добавьте подписку и вставьте скопированную ссылку подключения.</p>
                @if($piSubUrl)
                    <div class="inline-buttons">
                        <button type="button" class="btn btn-secondary btn-sm" data-import="happ">+ Добавить в Happ</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-import="v2raytun">+ Добавить в v2RayTun</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-copy>Копировать ссылку</button>
                    </div>
                @endif
            </div>
        </div>
        <div class="instruction-card">
            <div class="step-badge">3</div>
            <div class="step-content">
                <h3>Подключитесь</h3>
                <p>Выберите сервер и включите VPN. При первом запуске macOS попросит разрешение на установку VPN-профиля — подтвердите паролем.</p>
            </div>
        </div>
    </div>
</section>

@once
    @push('styles')
    <style>
        .platform-block {
            padding: 24px;
        }
        .platform-block-head {
            margin-bottom: 18px;
        }
        .platform-block-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 4px;
        }
        .platform-block-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.5;
        }

        .platform-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
            padding: 4px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .platform-tabs::-webkit-scrollbar { display: none; }

        .platform-tab {
            flex: 1 1 auto;
            min-width: 80px;
            padding: 9px 14px;
            background: transparent;
            border: none;
            border-radius: 6px;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: all var(--transition);
            white-space: nowrap;
        }
        .platform-tab:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.03);
        }
        .platform-tab.active {
            background: var(--red-primary);
            color: #fff;
            box-shadow: 0 0 12px rgba(220, 38, 38, 0.3);
        }

        .platform-instructions {
            display: none;
            flex-direction: column;
            gap: 10px;
        }
        .platform-instructions.active {
            display: flex;
        }

        .instruction-card {
            display: flex;
            gap: 14px;
            padding: 16px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            transition: border-color var(--transition);
        }
        .instruction-card:hover {
            border-color: rgba(220, 38, 38, 0.25);
        }

        .step-badge {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(220, 38, 38, 0.1);
            color: var(--red-light);
            border: 1px solid rgba(220, 38, 38, 0.25);
            border-radius: 50%;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .step-content {
            flex: 1;
            min-width: 0;
        }
        .step-content h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 4px;
        }
        .step-content p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.55;
            margin: 0;
        }

        .inline-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .inline-buttons .btn {
            text-decoration: none;
            font-size: 0.78rem;
            padding: 8px 14px;
        }

        @media (max-width: 640px) {
            .platform-block { padding: 18px; }

            .platform-tabs {
                gap: 2px;
            }
            .platform-tab {
                min-width: 72px;
                padding: 8px 10px;
                font-size: 0.8rem;
            }

            .instruction-card {
                padding: 14px;
                gap: 12px;
            }

            .step-badge {
                width: 26px;
                height: 26px;
                font-size: 0.78rem;
            }

            .inline-buttons .btn {
                flex: 1 1 auto;
                min-width: 140px;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    (function () {
        function initPlatformBlocks() {
            document.querySelectorAll('.platform-block').forEach(function (block) {
                var subUrl = block.getAttribute('data-sub-url') || '';

                block.querySelectorAll('.platform-tab').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        var platform = this.getAttribute('data-platform');
                        block.querySelectorAll('.platform-tab').forEach(function (t) { t.classList.remove('active'); });
                        block.querySelectorAll('.platform-instructions').forEach(function (s) { s.classList.remove('active'); });
                        this.classList.add('active');
                        var target = block.querySelector('.platform-instructions[data-platform="' + platform + '"]');
                        if (target) target.classList.add('active');
                    });
                });

                block.querySelectorAll('[data-import]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (!subUrl) return;
                        var app = this.getAttribute('data-import');
                        var scheme = app === 'happ' ? 'happ://import/' : 'v2raytun://import/';
                        window.location.href = scheme + encodeURIComponent(subUrl);
                    });
                });

                block.querySelectorAll('[data-copy]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (!subUrl) return;
                        var self = this;
                        var original = self.textContent;
                        var fallback = function () {
                            var ta = document.createElement('textarea');
                            ta.value = subUrl;
                            ta.style.position = 'fixed';
                            ta.style.opacity = '0';
                            document.body.appendChild(ta);
                            ta.select();
                            try { document.execCommand('copy'); } catch (e) {}
                            document.body.removeChild(ta);
                        };
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(subUrl).catch(fallback);
                        } else {
                            fallback();
                        }
                        self.textContent = 'Скопировано';
                        self.disabled = true;
                        setTimeout(function () {
                            self.textContent = original;
                            self.disabled = false;
                        }, 1500);
                    });
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPlatformBlocks);
        } else {
            initPlatformBlocks();
        }
    })();
    </script>
    @endpush
@endonce
