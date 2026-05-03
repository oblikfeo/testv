@extends('layouts.landing')

@push('styles')
    <link rel="stylesheet" href="@v('css/landing.css')">
@endpush

@section('title', 'AVA VPN — быстрый и приватный VPN для России | Бесплатный тест 8 часов')
@section('meta_description', 'AVA VPN — надёжный VPN-сервис для России: высокая скорость, защита от блокировок, простое подключение на телефон, компьютер и роутер. Тестовый доступ на 8 часов бесплатно, без банковской карты.')
@section('og_title', 'AVA VPN — быстрый VPN-сервис для России')
@section('og_description', 'Высокая скорость, стабильное соединение, защита приватности. 8 часов бесплатно — без карты и регистрации по номеру.')

@push('jsonld')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Product",
        "name": "AVA VPN",
        "description": "VPN-сервис для России с высокой скоростью, защитой от блокировок и поддержкой всех устройств. Подключение по протоколу VLESS, шифрование трафика, тестовый доступ бесплатно.",
        "brand": {
            "@@type": "Brand",
            "name": "AVA VPN"
        },
        "image": "{{ asset('assets/logo.png') }}",
        "offers": [
            {
                "@@type": "Offer",
                "name": "Стандартный — 30 дней",
                "description": "VPN-подписка на 30 дней, до 2 устройств",
                "price": "250",
                "priceCurrency": "RUB",
                "availability": "https://schema.org/InStock",
                "url": "{{ url('/#pricing') }}"
            },
            {
                "@@type": "Offer",
                "name": "Расширенный — 30 дней",
                "description": "VPN-подписка на 30 дней, до 5 устройств",
                "price": "550",
                "priceCurrency": "RUB",
                "availability": "https://schema.org/InStock",
                "url": "{{ url('/#pricing') }}"
            },
            {
                "@@type": "Offer",
                "name": "Стандартный — 180 дней",
                "description": "VPN-подписка на 180 дней, до 2 устройств",
                "price": "990",
                "priceCurrency": "RUB",
                "availability": "https://schema.org/InStock",
                "url": "{{ url('/#pricing') }}"
            },
            {
                "@@type": "Offer",
                "name": "Расширенный — 180 дней",
                "description": "VPN-подписка на 180 дней, до 5 устройств",
                "price": "2400",
                "priceCurrency": "RUB",
                "availability": "https://schema.org/InStock",
                "url": "{{ url('/#pricing') }}"
            }
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {
                "@@type": "Question",
                "name": "Что такое VPN и зачем он нужен?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "VPN (Virtual Private Network) — это технология, которая шифрует ваш интернет-трафик и направляет его через защищённый сервер. Это позволяет обходить блокировки сайтов, скрывать реальный IP-адрес и защищать данные при работе через публичные Wi-Fi сети."
                }
            },
            {
                "@@type": "Question",
                "name": "Будет ли AVA VPN работать в России?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Да. Мы используем современный протокол VLESS с маскировкой трафика, который стабильно работает на территории России и обходит DPI-блокировки. Серверы расположены в Нидерландах и Финляндии для минимальных задержек."
                }
            },
            {
                "@@type": "Question",
                "name": "Какие устройства поддерживаются?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "AVA VPN работает на всех популярных платформах: Android, iOS (iPhone и iPad), Windows, macOS, Linux, а также на роутерах с поддержкой XRay/VLESS. Достаточно установить любое совместимое приложение и импортировать конфигурацию."
                }
            },
            {
                "@@type": "Question",
                "name": "Можно ли попробовать бесплатно перед оплатой?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Да, при регистрации мы выдаём тестовый доступ на 8 часов абсолютно бесплатно. Банковская карта не требуется — нужен только email."
                }
            },
            {
                "@@type": "Question",
                "name": "Сколько устройств можно подключить одновременно?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Тариф «Стандартный» поддерживает до 2 устройств, тариф «Расширенный» — до 5 устройств одновременно. Вы можете подключить телефон, ноутбук, планшет и роутер в рамках одной подписки."
                }
            },
            {
                "@@type": "Question",
                "name": "Сохраняете ли вы логи?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Мы не ведём журналы посещаемых сайтов и не отслеживаем активность пользователей. Сохраняется только техническая информация, необходимая для биллинга и работы сервиса."
                }
            },
            {
                "@@type": "Question",
                "name": "Как оплатить подписку?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Оплата проходит через ЮKassa: банковской картой Visa, MasterCard, МИР, через СБП или СберPay. После оплаты подписка активируется автоматически в течение нескольких секунд."
                }
            },
            {
                "@@type": "Question",
                "name": "Что делать, если возникли проблемы с подключением?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Напишите в наш Telegram-чат поддержки — мы отвечаем быстро и помогаем с настройкой на любом устройстве. Ссылка на чат указана в шапке сайта."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "name": "Главная",
                "item": "{{ url('/') }}"
            }
        ]
    }
    </script>
@endpush

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-bg" aria-hidden="true">
            <div class="hero-orb hero-orb--1"></div>
            <div class="hero-orb hero-orb--2"></div>
            <div class="hero-grid"></div>
        </div>

        <div class="container hero-inner">
            <div class="hero-badge reveal">
                <span class="hero-badge-dot"></span>
                Серверы в ЕС · Подключение за 1 минуту
            </div>

            <h1 id="hero-title" class="reveal" style="--reveal-delay: 60ms">
                Быстрый VPN&nbsp;для&nbsp;России —<br>
                <span class="accent">без блокировок и&nbsp;тормозов</span>
            </h1>

            <p class="hero-subtitle reveal" style="--reveal-delay: 120ms">
                AVA VPN — это шифрование трафика, защита приватности и стабильная работа любимых сервисов.
                Подключите смартфон, компьютер или роутер за минуту и пользуйтесь интернетом без ограничений.
            </p>

            <div class="hero-cta reveal" style="--reveal-delay: 180ms">
                @auth
                    <a href="{{ route('keys.index') }}" class="btn btn-primary btn-lg">Перейти в кабинет →</a>
                    <span class="hint">Ваши ключи и активные подписки</span>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Получить тест бесплатно →</a>
                    <span class="hint">8 часов без оплаты. Карта не нужна.</span>
                @endauth
            </div>

            <ul class="hero-trust reveal" style="--reveal-delay: 240ms" aria-label="Преимущества">
                <li><span class="trust-check">✓</span> Без логов</li>
                <li><span class="trust-check">✓</span> Оплата ЮKassa</li>
                <li><span class="trust-check">✓</span> Поддержка 24/7</li>
                <li><span class="trust-check">✓</span> Возврат при сбое</li>
            </ul>
        </div>
    </section>

    <section class="stats-strip" aria-label="Ключевые свойства сервиса">
        <div class="container">
            <div class="stats-row">
                <span class="stat-word">Быстро</span>
                <span class="stat-dot" aria-hidden="true"></span>
                <span class="stat-word accent">Приватно</span>
                <span class="stat-dot" aria-hidden="true"></span>
                <span class="stat-word">Стабильно</span>
                <span class="stat-dot" aria-hidden="true"></span>
                <span class="stat-word accent">Просто</span>
            </div>
        </div>
    </section>

    <section class="section features" id="features" aria-labelledby="features-title">
        <div class="container">
            <header class="section-head reveal">
                <span class="section-eyebrow">Возможности</span>
                <h2 id="features-title" class="section-title">Почему выбирают AVA VPN</h2>
                <p class="section-subtitle">
                    Мы сделали VPN таким, каким он должен быть в&nbsp;2026 году: быстрый, стабильный и&nbsp;понятный.
                    Никаких сложных настроек и&nbsp;ограничений по&nbsp;скорости.
                </p>
            </header>

            <ul class="features-grid">
                <li class="feature-card reveal">
                    <div class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h7l-1 8 11-14h-7l1-6z"/></svg>
                    </div>
                    <h3>Высокая скорость</h3>
                    <p>Серверы 1&nbsp;Гбит/с в&nbsp;Европе и&nbsp;современный протокол VLESS Reality. Стримы 4K, видеозвонки и&nbsp;онлайн-игры — без лагов и&nbsp;«квадратов».</p>
                </li>
                <li class="feature-card reveal" style="--reveal-delay: 60ms">
                    <div class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.5 9.3 8 11 4.5-1.7 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3>Шифрование трафика</h3>
                    <p>TLS&nbsp;1.3, маскировка под обычный HTTPS и&nbsp;современные алгоритмы. Ваш интернет-провайдер видит только зашифрованный поток.</p>
                </li>
                <li class="feature-card reveal" style="--reveal-delay: 120ms">
                    <div class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                    </div>
                    <h3>Обход блокировок</h3>
                    <p>Доступ к&nbsp;Telegram, YouTube, Instagram*, ChatGPT, Discord и&nbsp;другим сервисам, которые ограничены или нестабильны в&nbsp;РФ.</p>
                </li>
                <li class="feature-card reveal" style="--reveal-delay: 180ms">
                    <div class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="14" rx="2"/><path d="M3 17h18M9 21h6"/></svg>
                    </div>
                    <h3>Любые устройства</h3>
                    <p>Android, iOS, Windows, macOS, Linux и&nbsp;роутеры. Один аккаунт — несколько устройств одновременно.</p>
                </li>
                <li class="feature-card reveal" style="--reveal-delay: 240ms">
                    <div class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </div>
                    <h3>Подключение&nbsp;за&nbsp;минуту</h3>
                    <p>Регистрация в&nbsp;один клик, готовая конфигурация в&nbsp;кабинете и&nbsp;понятные инструкции для каждой платформы.</p>
                </li>
                <li class="feature-card reveal" style="--reveal-delay: 300ms">
                    <div class="feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/><path d="M8 12h8M8 16h5"/></svg>
                    </div>
                    <h3>Поддержка в&nbsp;Telegram</h3>
                    <p>Реальные люди отвечают быстро. Помогут с&nbsp;настройкой, оплатой и&nbsp;ответят на&nbsp;любые вопросы.</p>
                </li>
            </ul>
        </div>
    </section>

    <section class="section how" id="how" aria-labelledby="how-title">
        <div class="container">
            <header class="section-head reveal">
                <span class="section-eyebrow">Подключение</span>
                <h2 id="how-title" class="section-title">Три шага — и&nbsp;вы&nbsp;в&nbsp;деле</h2>
                <p class="section-subtitle">Не&nbsp;нужно разбираться в&nbsp;настройках. Всё работает «из&nbsp;коробки».</p>
            </header>

            <ol class="steps">
                <li class="step reveal">
                    <div class="step-num">01</div>
                    <div class="step-body">
                        <h3>Создайте аккаунт</h3>
                        <p>Только email и&nbsp;пароль — больше ничего не&nbsp;нужно. Никаких паспортов и&nbsp;номеров телефона.</p>
                    </div>
                </li>
                <li class="step reveal" style="--reveal-delay: 80ms">
                    <div class="step-num">02</div>
                    <div class="step-body">
                        <h3>Получите конфигурацию</h3>
                        <p>В&nbsp;личном кабинете появится готовый ключ. Скопируйте ссылку или скачайте файл — всё в&nbsp;один клик.</p>
                    </div>
                </li>
                <li class="step reveal" style="--reveal-delay: 160ms">
                    <div class="step-num">03</div>
                    <div class="step-body">
                        <h3>Подключите устройство</h3>
                        <p>Импортируйте конфигурацию в&nbsp;приложение Happ, V2RayTun или любое совместимое — и&nbsp;интернет работает без&nbsp;ограничений.</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section class="section devices" id="devices" aria-labelledby="devices-title">
        <div class="container">
            <header class="section-head reveal">
                <span class="section-eyebrow">Платформы</span>
                <h2 id="devices-title" class="section-title">Работает на&nbsp;всех ваших устройствах</h2>
                <p class="section-subtitle">Один ключ — несколько устройств. Подключайте телефон, компьютер, планшет и&nbsp;роутер.</p>
            </header>

            <div class="devices-grid">
                <article class="device-card reveal">
                    <div class="device-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></svg>
                    </div>
                    <h3>Android</h3>
                    <p>Happ, V2RayTun, Hiddify, NekoBox</p>
                </article>
                <article class="device-card reveal" style="--reveal-delay: 60ms">
                    <div class="device-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 3a4 4 0 0 1-3.5 4M9 5a5 5 0 0 1 5 4 5 5 0 0 1 5 5c0 4-3 8-5 8s-2.5-1-5-1-3 1-5 1-5-4-5-8a5 5 0 0 1 5-5 5 5 0 0 1 5-4z"/></svg>
                    </div>
                    <h3>iPhone&nbsp;и&nbsp;iPad</h3>
                    <p>Happ, Streisand, V2Box, Foxray</p>
                </article>
                <article class="device-card reveal" style="--reveal-delay: 120ms">
                    <div class="device-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M8 22h8M12 18v4"/></svg>
                    </div>
                    <h3>Windows</h3>
                    <p>Hiddify, Nekoray, V2RayN, FlClash</p>
                </article>
                <article class="device-card reveal" style="--reveal-delay: 180ms">
                    <div class="device-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M2 21h20"/></svg>
                    </div>
                    <h3>macOS</h3>
                    <p>Hiddify, V2RayU, Streisand, Happ</p>
                </article>
                <article class="device-card reveal" style="--reveal-delay: 240ms">
                    <div class="device-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18v6H3zM3 14h18v6H3z"/><circle cx="7" cy="9" r=".5" fill="currentColor"/><circle cx="7" cy="17" r=".5" fill="currentColor"/></svg>
                    </div>
                    <h3>Роутер</h3>
                    <p>OpenWrt, Keenetic, Padavan</p>
                </article>
                <article class="device-card reveal" style="--reveal-delay: 300ms">
                    <div class="device-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
                    </div>
                    <h3>Linux</h3>
                    <p>Hiddify, Nekoray, sing-box CLI</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section pricing" id="pricing" aria-labelledby="pricing-title">
        <div class="container">
            <header class="section-head reveal">
                <span class="section-eyebrow">Тарифы</span>
                <h2 id="pricing-title" class="section-title">Честные цены без&nbsp;мелкого шрифта</h2>
                <p class="section-subtitle">Два плана, прозрачные условия и&nbsp;скидки за&nbsp;долгий период. Без скрытых платежей и&nbsp;автосписаний.</p>
            </header>

            <div class="pricing-table-wrap reveal">
                <table class="pricing-table" aria-describedby="pricing-title">
                    <caption class="visually-hidden">Стоимость подписки AVA VPN по&nbsp;тарифам и&nbsp;периодам</caption>
                    <thead>
                        <tr>
                            <th scope="col">Период</th>
                            <th scope="col">
                                <div class="plan-name">Стандартный</div>
                                <div class="plan-desc">2 устройства</div>
                            </th>
                            <th scope="col" class="plan-highlight">
                                <span class="plan-badge">выгодно</span>
                                <div class="plan-name">Расширенный</div>
                                <div class="plan-desc">5 устройств</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">30 дней</th>
                            <td><strong>250&nbsp;₽</strong></td>
                            <td class="plan-highlight"><strong>550&nbsp;₽</strong></td>
                        </tr>
                        <tr>
                            <th scope="row">90 дней</th>
                            <td><strong>600&nbsp;₽</strong> <span class="table-badge">−20%</span></td>
                            <td class="plan-highlight"><strong>1&nbsp;350&nbsp;₽</strong> <span class="table-badge">−18%</span></td>
                        </tr>
                        <tr>
                            <th scope="row">180 дней</th>
                            <td><strong>990&nbsp;₽</strong> <span class="table-badge">−34%</span></td>
                            <td class="plan-highlight"><strong>2&nbsp;400&nbsp;₽</strong> <span class="table-badge">−27%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pricing-cta reveal">
                @auth
                    <a href="{{ route('keys.index') }}" class="btn btn-primary btn-lg">Перейти в&nbsp;кабинет →</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Оформить подписку →</a>
                @endauth
            </div>

            <ul class="pricing-notes reveal">
                <li class="pricing-note"><span class="check">✓</span> Оплата ЮKassa: карта, СБП, СберPay</li>
                <li class="pricing-note"><span class="check">✓</span> Без автосписаний</li>
                <li class="pricing-note"><span class="check">✓</span> Возврат при технических проблемах</li>
            </ul>
        </div>
    </section>

    <section class="section compare" id="compare" aria-labelledby="compare-title">
        <div class="container">
            <header class="section-head reveal">
                <span class="section-eyebrow">Сравнение</span>
                <h2 id="compare-title" class="section-title">AVA VPN против бесплатных и&nbsp;«серых» сервисов</h2>
                <p class="section-subtitle">Почему платный VPN с&nbsp;собственной инфраструктурой надёжнее, чем «бесплатные» решения.</p>
            </header>

            <div class="compare-grid">
                <article class="compare-card compare-card--bad reveal">
                    <h3>Бесплатные VPN</h3>
                    <ul>
                        <li>Низкая скорость и&nbsp;ограничения по&nbsp;трафику</li>
                        <li>Часто продают данные пользователей и&nbsp;показывают рекламу</li>
                        <li>Нестабильно работают, серверы перегружены</li>
                        <li>Поддержки нет — пишите на&nbsp;форум и&nbsp;ждите</li>
                    </ul>
                </article>
                <article class="compare-card compare-card--good reveal" style="--reveal-delay: 80ms">
                    <h3>AVA VPN</h3>
                    <ul>
                        <li>Без лимитов скорости и&nbsp;трафика</li>
                        <li>Не&nbsp;продаём данные, не&nbsp;показываем рекламу</li>
                        <li>Собственные серверы, мониторинг 24/7</li>
                        <li>Живая поддержка в&nbsp;Telegram, ответ&nbsp;— минуты</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="section faq" id="faq" aria-labelledby="faq-title">
        <div class="container">
            <header class="section-head reveal">
                <span class="section-eyebrow">FAQ</span>
                <h2 id="faq-title" class="section-title">Частые вопросы о&nbsp;VPN-сервисе</h2>
                <p class="section-subtitle">Если не&nbsp;нашли ответа — напишите нам в&nbsp;<a href="{{ config('app.telegram_support_url') }}" target="_blank" rel="noopener">Telegram-чат поддержки</a>.</p>
            </header>

            <div class="faq-list reveal">
                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Что такое VPN и&nbsp;зачем он&nbsp;нужен?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>VPN (Virtual Private Network) — это технология, которая шифрует ваш интернет-трафик и&nbsp;направляет его через защищённый сервер. Это позволяет обходить блокировки сайтов, скрывать реальный IP-адрес и&nbsp;защищать данные при работе через публичные Wi-Fi сети.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Будет ли AVA VPN работать в&nbsp;России?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>Да. Мы используем современный протокол VLESS с&nbsp;маскировкой трафика, который стабильно работает на&nbsp;территории России и&nbsp;обходит DPI-блокировки. Серверы расположены в&nbsp;Нидерландах и&nbsp;Финляндии — это даёт минимальные задержки.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Какие устройства поддерживаются?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>AVA VPN работает на&nbsp;всех популярных платформах: Android, iOS (iPhone и&nbsp;iPad), Windows, macOS, Linux, а&nbsp;также на&nbsp;роутерах с&nbsp;поддержкой XRay/VLESS. Достаточно установить любое совместимое приложение и&nbsp;импортировать конфигурацию из&nbsp;кабинета.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Можно&nbsp;ли попробовать бесплатно перед оплатой?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>Да, при регистрации мы&nbsp;выдаём тестовый доступ на&nbsp;8 часов абсолютно бесплатно. Банковская карта не&nbsp;требуется — нужен только email.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Сколько устройств можно подключить одновременно?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>Тариф «Стандартный» поддерживает до&nbsp;2&nbsp;устройств, тариф «Расширенный» — до&nbsp;5&nbsp;устройств одновременно. Вы&nbsp;можете подключить телефон, ноутбук, планшет и&nbsp;роутер в&nbsp;рамках одной подписки.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Сохраняете&nbsp;ли вы&nbsp;логи?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>Мы&nbsp;не&nbsp;ведём журналы посещаемых сайтов и&nbsp;не&nbsp;отслеживаем активность пользователей. Сохраняется только техническая информация, необходимая для биллинга и&nbsp;работы сервиса.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Как оплатить подписку?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>Оплата проходит через ЮKassa: банковской картой Visa, MasterCard, МИР, через СБП или СберPay. После оплаты подписка активируется автоматически в&nbsp;течение нескольких секунд.</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-q">Что делать, если возникли проблемы с&nbsp;подключением?</span>
                        <span class="faq-toggle" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        <p>Напишите в&nbsp;наш <a href="{{ config('app.telegram_support_url') }}" target="_blank" rel="noopener">Telegram-чат поддержки</a> — мы&nbsp;отвечаем быстро и&nbsp;помогаем с&nbsp;настройкой на&nbsp;любом устройстве.</p>
                    </div>
                </details>
            </div>
        </div>
    </section>

    <section class="section cta-final" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta-card reveal">
                <div class="cta-glow" aria-hidden="true"></div>
                <h2 id="cta-title">Попробуйте AVA&nbsp;VPN прямо сейчас</h2>
                <p>8&nbsp;часов бесплатного тестового доступа без банковской карты.<br>Если понравится — продолжите с&nbsp;любого тарифа.</p>
                <div class="cta-buttons">
                    @auth
                        <a href="{{ route('keys.index') }}" class="btn btn-primary btn-lg">Перейти в&nbsp;кабинет →</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Получить тест бесплатно →</a>
                    @endauth
                    <a href="{{ config('app.telegram_support_url') }}" target="_blank" rel="noopener" class="btn btn-secondary btn-lg">Задать вопрос в&nbsp;Telegram</a>
                </div>
            </div>
        </div>
    </section>

    <p class="footnote container">
        * Instagram&nbsp;— проект Meta, признан в&nbsp;РФ экстремистским и&nbsp;запрещён.
    </p>
@endsection
