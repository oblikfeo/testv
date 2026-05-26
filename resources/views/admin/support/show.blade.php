@extends('admin.layout')

@section('title', 'Тикет #'.$ticket->id)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.support.index') }}" class="text-gray-400 hover:text-white text-sm">← К списку</a>
</div>

<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white">#{{ $ticket->id }} — {{ $ticket->subject }}</h1>
        <p class="text-gray-400 mt-1 text-sm">{{ $ticket->categoryLabel() }} · создан {{ $ticket->created_at->format('d.m.Y H:i') }}</p>
    </div>
    @php
        $badge = match($ticket->status) {
            'open' => 'bg-yellow-500/15 text-yellow-300',
            'pending_user' => 'bg-green-500/15 text-green-300',
            'closed' => 'bg-gray-700 text-gray-400',
            default => 'bg-gray-700 text-gray-300',
        };
    @endphp
    <span class="inline-flex items-center px-3 py-1.5 rounded text-sm font-medium {{ $badge }}">
        {{ $ticket->statusLabel() }}
    </span>
</div>

@if(session('success'))
    <div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-400">{{ session('success') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 mb-6">
        <p class="text-red-400">{{ $errors->first() }}</p>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-3">
        @foreach($ticket->messages as $message)
            <div class="rounded-lg p-4 border {{ $message->isAdmin() ? 'bg-blue-500/5 border-blue-500/30' : 'bg-gray-800 border-gray-700' }}">
                <div class="flex justify-between items-baseline mb-2 gap-3 flex-wrap">
                    <span class="font-semibold text-white text-sm">
                        @if($message->isAdmin())
                            Поддержка
                        @else
                            {{ $message->authorUser?->email ?? 'Пользователь' }}
                        @endif
                    </span>
                    <span class="text-xs text-gray-500">{{ $message->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="text-gray-200 text-sm whitespace-pre-wrap break-words leading-relaxed">{{ $message->body }}</div>
            </div>
        @endforeach

        @if($ticket->status !== 'closed')
            <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                @csrf
                <label class="block text-sm text-gray-400 mb-2">Ответ пользователю</label>
                <textarea name="body" rows="5" maxlength="5000" required
                          class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm">{{ old('body') }}</textarea>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Отправить</button>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.support.close', $ticket) }}" class="mt-2">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-white">Закрыть тикет</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.support.reopen', $ticket) }}" class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                @csrf
                <p class="text-gray-400 text-sm mb-3">Тикет закрыт.</p>
                <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm">Переоткрыть</button>
            </form>
        @endif
    </div>

    <aside class="space-y-4">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <h2 class="text-sm font-semibold text-gray-300 mb-3 uppercase tracking-wider">Пользователь</h2>
            <dl class="text-sm space-y-2">
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-white break-all">{{ $ticket->user->email ?? '—' }}</dd>
                </div>
                @if($ticket->user?->name)
                    <div>
                        <dt class="text-gray-500">Имя</dt>
                        <dd class="text-white">{{ $ticket->user->name }}</dd>
                    </div>
                @endif
                @if($ticket->user?->telegram_username)
                    <div>
                        <dt class="text-gray-500">Telegram</dt>
                        <dd class="text-white">@ {{ $ticket->user->telegram_username }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500">User ID</dt>
                    <dd class="text-white">{{ $ticket->user_id }}</dd>
                </div>
            </dl>
        </div>

        @if($ticket->meta)
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                <h2 class="text-sm font-semibold text-gray-300 mb-3 uppercase tracking-wider">Контекст</h2>
                <dl class="text-sm space-y-2">
                    @if(! empty($ticket->meta['subscription_id']))
                        <div>
                            <dt class="text-gray-500">Активная подписка</dt>
                            <dd class="text-white">#{{ $ticket->meta['subscription_id'] }}</dd>
                        </div>
                    @endif
                    @if(! empty($ticket->meta['last_order_id']))
                        <div>
                            <dt class="text-gray-500">Последний заказ</dt>
                            <dd class="text-white">#{{ $ticket->meta['last_order_id'] }}</dd>
                        </div>
                    @endif
                    @if(! empty($ticket->meta['ip']))
                        <div>
                            <dt class="text-gray-500">IP</dt>
                            <dd class="text-white">{{ $ticket->meta['ip'] }}</dd>
                        </div>
                    @endif
                    @if(! empty($ticket->meta['user_agent']))
                        <div>
                            <dt class="text-gray-500">User-Agent</dt>
                            <dd class="text-white text-xs break-all">{{ $ticket->meta['user_agent'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
    </aside>
</div>
@endsection
