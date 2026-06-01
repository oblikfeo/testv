@extends('layouts.cabinet')

@section('title', 'Поддержка')

@section('content')
<div class="support-page">
    <header class="support-hero">
        <h1 class="cab-page-title">Поддержка</h1>
        <p class="cab-page-desc">
            Опишите проблему — поможем с подключением, оплатой или возвратом.
            Ответ придёт здесь; при привязанном Telegram продублируем уведомление.
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

    <div class="support-stack">
        <section class="cab-card support-card support-card--form" aria-labelledby="support-new-title">
            <div class="support-section-head">
                <div class="support-section-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        <path d="M12 7v6"/><path d="M9 10h6"/>
                    </svg>
                </div>
                <div>
                    <h2 id="support-new-title" class="support-section-title">Новое обращение</h2>
                    <p class="support-section-desc">Укажите тему и опишите ситуацию — чем подробнее, тем быстрее поможем</p>
                </div>
            </div>

            <form method="POST" action="{{ route('cabinet.support.store') }}" class="support-form">
                @csrf
                <div class="support-form-grid">
                    <div class="support-field support-field--full">
                        <label for="support-subject">Тема</label>
                        <input type="text" id="support-subject" name="subject" maxlength="200" required
                               value="{{ old('subject') }}"
                               placeholder="Например: не подключается VPN на iPhone">
                    </div>
                    <div class="support-field">
                        <label for="support-category">Категория</label>
                        <select id="support-category" name="category" required>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', 'connection') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="support-field">
                    <label for="support-body">Сообщение</label>
                    <textarea id="support-body" name="body" rows="6" maxlength="5000" required
                              placeholder="Устройство, приложение (Happ / v2RayTun), текст ошибки, скриншоты — если есть">{{ old('body') }}</textarea>
                </div>
                <div class="support-form-footer">
                    <button type="submit" class="btn btn-primary support-submit">Отправить обращение</button>
                </div>
            </form>
        </section>

        <section class="cab-card support-card support-card--list" aria-labelledby="support-list-title">
            <div class="support-section-head support-section-head--row">
                <div>
                    <h2 id="support-list-title" class="support-section-title">Ваши обращения</h2>
                    <p class="support-section-desc">История переписки с поддержкой</p>
                </div>
                @if($tickets->total() > 0)
                    <span class="support-count">{{ $tickets->total() }}</span>
                @endif
            </div>

            @if($tickets->count() > 0)
                <ul class="support-tickets">
                    @foreach($tickets as $ticket)
                        <li>
                            <a href="{{ route('cabinet.support.show', $ticket) }}" class="support-ticket-link">
                                <div class="support-ticket-main">
                                    <span class="support-ticket-id">#{{ $ticket->id }}</span>
                                    <span class="support-ticket-subject">{{ $ticket->subject }}</span>
                                    <span class="support-ticket-meta">
                                        {{ $ticket->categoryLabel() }}
                                        · {{ optional($ticket->last_message_at ?? $ticket->created_at)->format('d.m.Y H:i') }}
                                    </span>
                                </div>
                                <span class="support-status support-status--{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if($tickets->hasPages())
                    <div class="support-pagination">{{ $tickets->links() }}</div>
                @endif
            @else
                <div class="support-empty">
                    <p>Обращений пока нет — форма выше всегда доступна.</p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
