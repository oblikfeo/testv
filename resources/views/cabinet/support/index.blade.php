@extends('layouts.cabinet')

@section('title', 'Поддержка')

@section('content')
    <h1 class="cab-page-title">Поддержка</h1>
    <p class="cab-page-desc">Опишите проблему — наша команда поможет с подключением, оплатой или возвратом. Мы отвечаем в личном кабинете и дублируем в Telegram, если он привязан.</p>

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

    <div class="cab-card">
        <div class="cab-card-header">
            <span class="cab-card-title">Новое обращение</span>
        </div>
        <form method="POST" action="{{ route('cabinet.support.store') }}" class="support-form">
            @csrf
            <div class="support-row">
                <label class="support-label" for="support-subject">Тема</label>
                <input type="text" id="support-subject" name="subject" maxlength="200" required
                       value="{{ old('subject') }}"
                       placeholder="Кратко опишите проблему" class="support-input">
            </div>
            <div class="support-row">
                <label class="support-label" for="support-category">Категория</label>
                <select id="support-category" name="category" required class="support-input">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="support-row">
                <label class="support-label" for="support-body">Сообщение</label>
                <textarea id="support-body" name="body" rows="6" maxlength="5000" required
                          placeholder="Что произошло, какое устройство и приложение, какие ошибки видите"
                          class="support-input">{{ old('body') }}</textarea>
            </div>
            <div class="support-actions">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </div>
        </form>
    </div>

    <div class="cab-card mt-24">
        <div class="cab-card-header">
            <span class="cab-card-title">Ваши обращения</span>
        </div>

        @if($tickets->count() > 0)
            <ul class="support-list">
                @foreach($tickets as $ticket)
                    <li class="support-list-item">
                        <a href="{{ route('cabinet.support.show', $ticket) }}" class="support-list-link">
                            <div class="support-list-head">
                                <span class="support-list-subject">#{{ $ticket->id }} · {{ $ticket->subject }}</span>
                                <span class="support-status support-status--{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span>
                            </div>
                            <div class="support-list-meta">
                                <span>{{ $ticket->categoryLabel() }}</span>
                                <span>· {{ optional($ticket->last_message_at ?? $ticket->created_at)->format('d.m.Y H:i') }}</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if($tickets->hasPages())
                <div class="pagination-wrap">{{ $tickets->links() }}</div>
            @endif
        @else
            <div class="cab-empty"><p>Обращений пока нет.</p></div>
        @endif
    </div>
@endsection

@push('styles')
<style>
.alert { padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem; }
.alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #22c55e; }
.alert-error { background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.2); color: var(--red-light); }

.support-form { padding: 16px 20px 20px; display: flex; flex-direction: column; gap: 14px; }
.support-row { display: flex; flex-direction: column; gap: 6px; }
.support-label { font-size: 0.85rem; color: var(--text-secondary); }
.support-input {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    color: var(--text-primary);
    font-family: var(--font-main);
    font-size: 0.95rem;
    width: 100%;
    transition: border-color var(--transition);
}
.support-input:focus { border-color: var(--red-primary); outline: none; }
textarea.support-input { resize: vertical; min-height: 120px; }
.support-actions { display: flex; justify-content: flex-end; }

.support-list { padding: 8px 0; }
.support-list-item + .support-list-item { border-top: 1px solid var(--border-color); }
.support-list-link {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 14px 20px;
    color: inherit;
    transition: background var(--transition);
}
.support-list-link:hover { background: rgba(255, 255, 255, 0.02); color: inherit; }
.support-list-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
.support-list-subject { font-weight: 600; color: var(--text-primary); }
.support-list-meta { color: var(--text-muted); font-size: 0.8rem; display: flex; gap: 6px; flex-wrap: wrap; }

.support-status {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 100px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.support-status--open { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.support-status--pending_user { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
.support-status--closed { background: rgba(255, 255, 255, 0.06); color: var(--text-muted); }
</style>
@endpush
