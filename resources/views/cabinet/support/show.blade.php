@extends('layouts.cabinet')

@section('title', 'Тикет #'.$ticket->id)

@section('content')
    <div class="support-show-head">
        <a href="{{ route('cabinet.support.index') }}" class="support-back">← К списку</a>
        <span class="support-status support-status--{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span>
    </div>

    <h1 class="cab-page-title">#{{ $ticket->id }} — {{ $ticket->subject }}</h1>
    <p class="cab-page-desc">{{ $ticket->categoryLabel() }} · создан {{ $ticket->created_at->format('d.m.Y H:i') }}</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="support-thread">
        @foreach($ticket->messages as $message)
            <div class="support-msg support-msg--{{ $message->author_type }}">
                <div class="support-msg-head">
                    <span class="support-msg-author">
                        @if($message->isAdmin())
                            Поддержка AVA VPN
                        @else
                            Вы
                        @endif
                    </span>
                    <span class="support-msg-time">{{ $message->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="support-msg-body">{{ $message->body }}</div>
            </div>
        @endforeach
    </div>

    @if($ticket->isOpen())
        <div class="cab-card mt-24">
            <div class="cab-card-header">
                <span class="cab-card-title">Ответить</span>
            </div>
            <form method="POST" action="{{ route('cabinet.support.reply', $ticket) }}" class="support-form">
                @csrf
                <textarea name="body" rows="5" maxlength="5000" required
                          placeholder="Опишите детали или приложите ID транзакции"
                          class="support-input">{{ old('body') }}</textarea>
                <div class="support-actions">
                    <button type="submit" class="btn btn-primary">Отправить</button>
                </div>
            </form>
            <form method="POST" action="{{ route('cabinet.support.close', $ticket) }}"
                  onsubmit="return confirm('Закрыть тикет? Если вопрос вернётся — создайте новое обращение.');"
                  class="support-close-row">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Закрыть тикет</button>
            </form>
        </div>
    @else
        <div class="cab-card mt-24">
            <div class="cab-empty">
                <p>Тикет закрыт. <a href="{{ route('cabinet.support.index') }}">Создать новое обращение</a>.</p>
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
.alert { padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem; }
.alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #22c55e; }
.alert-error { background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.2); color: var(--red-light); }

.support-show-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 12px; flex-wrap: wrap; }
.support-back { color: var(--text-secondary); font-size: 0.85rem; }
.support-back:hover { color: var(--text-primary); }

.support-status { font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.5px; }
.support-status--open { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.support-status--pending_user { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
.support-status--closed { background: rgba(255, 255, 255, 0.06); color: var(--text-muted); }

.support-thread { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; }
.support-msg {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 14px 16px;
}
.support-msg--admin { border-color: rgba(220, 38, 38, 0.35); background: rgba(220, 38, 38, 0.04); }
.support-msg-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; gap: 12px; flex-wrap: wrap; }
.support-msg-author { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
.support-msg-time { color: var(--text-muted); font-size: 0.8rem; }
.support-msg-body { color: var(--text-primary); white-space: pre-wrap; word-break: break-word; line-height: 1.55; }

.support-form { padding: 16px 20px 20px; display: flex; flex-direction: column; gap: 14px; }
.support-input {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    color: var(--text-primary);
    font-family: var(--font-main);
    font-size: 0.95rem;
    width: 100%;
    resize: vertical;
    min-height: 120px;
}
.support-input:focus { border-color: var(--red-primary); outline: none; }
.support-actions { display: flex; justify-content: flex-end; gap: 12px; }
.support-close-row { padding: 0 20px 20px; display: flex; justify-content: flex-start; }
</style>
@endpush
