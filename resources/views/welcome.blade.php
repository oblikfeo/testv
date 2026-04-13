@extends('layouts.landing')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
@endpush

@section('title', 'AVA VPN — безграничный доступ к сети')

@section('content')
    <section class="hero">
        <div class="container">
            <div class="hero-badge">Работает там, где другие не могут</div>
            <h1>Твой интернет —<br><span class="accent">без границ</span></h1>
            <p class="hero-subtitle">
                Мощная защита канала, обход любых ограничений и стабильный пинг.
                Подключил — и забыл. Всё работает само.
            </p>
            <div class="hero-cta">
                @auth
                    <a href="{{ route('keys.index') }}" class="btn btn-primary btn-lg">Мои ключи →</a>
                    <span class="hint">Личный кабинет и конфигурации</span>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Тестовый доступ бесплатно →</a>
                    <span class="hint">8 часов без оплаты. Карта не нужна.</span>
                @endauth
            </div>
        </div>
    </section>

    <section class="stats-strip">
        <div class="container">
            <div class="stats-row">
                <span class="stat-word">Быстро</span>
                <span class="stat-dot"></span>
                <span class="stat-word accent">Приватно</span>
                <span class="stat-dot"></span>
                <span class="stat-word">Стабильно</span>
                <span class="stat-dot"></span>
                <span class="stat-word accent">Без границ</span>
            </div>
        </div>
    </section>

    <section class="section how" id="how">
        <div class="container">
            <h2 class="section-title">Три шага — и вы в деле</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-num">01</div>
                    <div class="step-body">
                        <h3>Создайте аккаунт</h3>
                        <p>Только email и пароль — больше ничего не нужно. Никаких паспортов и номеров телефона.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">02</div>
                    <div class="step-body">
                        <h3>Получите конфигурацию</h3>
                        <p>В личном кабинете появится готовый файл. Скопируйте ссылку или скачайте — один клик.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">03</div>
                    <div class="step-body">
                        <h3>Подключите на устройстве</h3>
                        <p>Вставьте конфигурацию в приложение. Телефон, компьютер, роутер — работает везде.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section pricing" id="pricing">
        <div class="container">
            <h2 class="section-title">Тарифы</h2>
            <p class="section-subtitle">Всё просто: два плана, честные цены, без мелкого шрифта.</p>

            <div class="pricing-table-wrap">
                <table class="pricing-table">
                    <thead>
                        <tr>
                            <th>Период</th>
                            <th>
                                <div class="plan-name">Стандартный</div>
                                <div class="plan-desc">2 устройства</div>
                            </th>
                            <th class="plan-highlight">
                                <span class="plan-badge">выгодно</span>
                                <div class="plan-name">Расширенный</div>
                                <div class="plan-desc">5 устройств</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>30 дней</td>
                            <td><strong>250 ₽</strong></td>
                            <td class="plan-highlight"><strong>550 ₽</strong></td>
                        </tr>
                        <tr>
                            <td>90 дней</td>
                            <td><strong>600 ₽</strong> <span class="table-badge">−20%</span></td>
                            <td class="plan-highlight"><strong>1 350 ₽</strong> <span class="table-badge">−18%</span></td>
                        </tr>
                        <tr>
                            <td>180 дней</td>
                            <td><strong>990 ₽</strong> <span class="table-badge">−34%</span></td>
                            <td class="plan-highlight"><strong>2 400 ₽</strong> <span class="table-badge">−27%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pricing-cta">
                @auth
                    <a href="{{ route('keys.index') }}" class="btn btn-primary btn-lg">Перейти в кабинет →</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Оформить подписку →</a>
                @endauth
            </div>

            <div class="pricing-notes">
                <div class="pricing-note">
                    <span class="check">✓</span>
                    <span>Карты РФ и СБП</span>
                </div>
                <div class="pricing-note">
                    <span class="check">✓</span>
                    <span>Без автосписаний — продлеваете сами</span>
                </div>
                <div class="pricing-note">
                    <span class="check">✓</span>
                    <span>Возврат в течение 24 часов</span>
                </div>
            </div>
        </div>
    </section>
@endsection
