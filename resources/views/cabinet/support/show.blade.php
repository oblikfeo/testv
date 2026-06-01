@extends('layouts.cabinet')

@section('title', 'Тикет #'.$ticket->id)

@section('content')
<div class="support-page support-show-page">
    <div class="support-show-toolbar">
        <a href="{{ route('cabinet.support.index') }}" class="support-back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
            </svg>
            К списку обращений
        </a>
        <span class="support-status support-status--{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span>
    </div>

    <header class="support-ticket-hero cab-card">
        <h1 class="support-ticket-title">
            <span class="support-ticket-num">#{{ $ticket->id }}</span>
            {{ $ticket->subject }}
        </h1>
        <p class="support-ticket-desc">
            {{ $ticket->categoryLabel() }}
            · создан {{ $ticket->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
        </p>
    </header>

    @if(session('success'))
        <div class="sub-alert sub-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="sub-alert sub-alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="support-thread" role="log" aria-label="Переписка">
        @foreach($ticket->messages as $message)
            <article class="support-bubble support-bubble--{{ $message->isAdmin() ? 'admin' : 'user' }}">
                <div class="support-bubble-head">
                    <span class="support-bubble-author">
                        @if($message->isAdmin())
                            <span class="support-bubble-avatar support-bubble-avatar--admin" aria-hidden="true">A</span>
                            Поддержка AVA VPN
                        @else
                            <span class="support-bubble-avatar" aria-hidden="true">Вы</span>
                            Вы
                        @endif
                    </span>
                    <time class="support-bubble-time" datetime="{{ $message->created_at->toIso8601String() }}">
                        {{ $message->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                    </time>
                </div>
                <div class="support-bubble-body">{{ $message->body }}</div>
            </article>
        @endforeach
    </div>

    @if($ticket->isOpen())
        <section class="cab-card support-card support-card--reply" aria-labelledby="support-reply-title">
            <div class="support-section-head">
                <h2 id="support-reply-title" class="support-section-title">Ваш ответ</h2>
                <p class="support-section-desc">Дополните детали или приложите ID платежа</p>
            </div>

            <form id="support-reply-form" method="POST" action="{{ route('cabinet.support.reply', $ticket) }}" class="support-form">
                @csrf
                <div class="support-field">
                    <label for="support-reply-body" class="sr-only">Сообщение</label>
                    <textarea id="support-reply-body" name="body" rows="5" maxlength="5000" required
                              placeholder="Напишите ответ поддержке…">{{ old('body') }}</textarea>
                </div>
            </form>
            <div class="support-form-footer support-form-footer--split">
                <form method="POST" action="{{ route('cabinet.support.close', $ticket) }}"
                      onsubmit="return confirm('Закрыть тикет? Если вопрос вернётся — создайте новое обращение.');"
                      class="support-close-form">
                    @csrf
                    <button type="submit" class="btn btn-secondary support-submit">Закрыть тикет</button>
                </form>
                <button type="submit" form="support-reply-form" class="btn btn-primary support-submit">Отправить</button>
            </div>
        </section>
    @else
        <div class="cab-card support-card support-card--closed">
            <p class="support-closed-text">
                Тикет закрыт. Если нужна помощь —
                <a href="{{ route('cabinet.support.index') }}">создайте новое обращение</a>.
            </p>
        </div>
    @endif
</div>
@endsection
