@extends('admin.layout')

@section('title', 'Отзывы после теста')

@section('content')
<div class="bg-gray-800 rounded-xl p-6">
    <h1 class="text-2xl font-bold text-white mb-2">Отзывы после пробного доступа</h1>
    <p class="text-gray-400 mb-6">
        Сообщения из Telegram-бота, которые пользователи отправили после завершения тестового периода.
    </p>

    @if ($items->isEmpty())
        <div class="rounded-lg border border-gray-700 bg-gray-900/40 p-4 text-gray-300">
            Пока отзывов нет.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($items as $item)
                <article class="rounded-lg border border-gray-700 bg-gray-900/40 p-4">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-400 mb-3">
                        <span>#{{ $item->id }}</span>
                        <span>{{ $item->created_at?->format('d.m.Y H:i') }}</span>
                        <span>trigger: {{ $item->trigger }}</span>
                    </div>

                    <div class="text-sm text-gray-300 mb-3">
                        <div>Email: <span class="text-white">{{ $item->user?->email ?? '—' }}</span></div>
                        <div>Telegram: <span class="text-white">{{ $item->telegram_username ? '@'.$item->telegram_username : '—' }}</span></div>
                        <div>Telegram ID: <span class="text-white">{{ $item->telegram_id ?? '—' }}</span></div>
                    </div>

                    <div class="rounded border border-gray-700 bg-gray-950 p-3 text-gray-100 whitespace-pre-wrap">{{ $item->message }}</div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
