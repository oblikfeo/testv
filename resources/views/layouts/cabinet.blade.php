<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Кабинет')) — AVA VPN</title>
    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cabinet.css') }}">
    @stack('styles')
</head>
<body>
    @include('partials.cabinet-navbar')
    <div class="cabinet">
        @include('partials.cabinet-sidebar', ['activeRoute' => $activeRoute ?? ''])
        <main class="cab-main">
            @auth
                @if (! auth()->user()->hasVerifiedEmail())
                    <div class="cab-verify-bar" role="status" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; background: rgba(220, 38, 38, 0.12); border: 1px solid rgba(220, 38, 38, 0.35); color: #fecaca; font-size: 0.9rem;">
                        <strong style="color: #fca5a5;">Подтвердите email.</strong>
                        Проверьте почту и перейдите по ссылке из письма. Не пришло —
                        <form method="POST" action="{{ route('verification.send') }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: #fca5a5; text-decoration: underline; cursor: pointer; padding: 0; font: inherit;">отправить ещё раз</button>
                        </form>
                        или
                        <a href="{{ route('verification.notice') }}" style="color: #fca5a5; text-decoration: underline;">страница подтверждения</a>.
                    </div>
                @endif

                @if(($pendingTrialFeedbackRequest ?? null) !== null)
                    <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #dbeafe;">
                        <strong style="color: #bfdbfe;">Помогите нам стать лучше.</strong>
                        Ваш тестовый период завершился — поделитесь, пожалуйста, обратной связью по скорости и стабильности.
                        <form method="POST" action="{{ route('cabinet.trial-feedback.submit') }}" style="margin-top: 10px;">
                            @csrf
                            <textarea
                                name="message"
                                rows="3"
                                maxlength="4000"
                                required
                                placeholder="Напишите в свободной форме: где всё хорошо, а где были проблемы"
                                style="width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.18); background: rgba(2,6,23,0.45); color: #fff; padding: 10px 12px; resize: vertical;"
                            ></textarea>
                            <button type="submit" style="margin-top: 10px; padding: 8px 14px; border-radius: 8px; border: none; background: #3b82f6; color: #fff; cursor: pointer;">
                                Отправить отзыв
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
