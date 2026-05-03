@extends('admin.layout')

@section('title', 'Поддержка')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white">Поддержка</h1>
        <p class="text-gray-400 mt-1">Тикеты пользователей и переписка.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @php
            $tabs = [
                'active' => 'Активные ('.($counters['open'] + $counters['pending_user']).')',
                'open' => 'Ждут ответа ('.$counters['open'].')',
                'pending_user' => 'У пользователя ('.$counters['pending_user'].')',
                'closed' => 'Закрытые ('.$counters['closed'].')',
            ];
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.support.index', ['status' => $key]) }}"
               class="px-3 py-2 rounded-md text-sm font-medium {{ $filter === $key ? 'bg-blue-600 text-white' : 'bg-gray-700/60 text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

@if(session('success'))
    <div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-400">{{ session('success') }}</p>
    </div>
@endif

<div class="bg-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-750">
                <tr class="text-left text-xs text-gray-400 uppercase tracking-wider">
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Тема</th>
                    <th class="px-4 py-3">Категория</th>
                    <th class="px-4 py-3">Пользователь</th>
                    <th class="px-4 py-3">Статус</th>
                    <th class="px-4 py-3">Сообщений</th>
                    <th class="px-4 py-3">Обновлён</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-4 py-3 text-gray-300">
                            <a href="{{ route('admin.support.show', $ticket) }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ $ticket->id }}</a>
                        </td>
                        <td class="px-4 py-3 text-white">
                            <a href="{{ route('admin.support.show', $ticket) }}" class="hover:text-blue-300">{{ $ticket->subject }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $ticket->categoryLabel() }}</td>
                        <td class="px-4 py-3 text-gray-300">
                            <div>{{ $ticket->user->email ?? '—' }}</div>
                            @if($ticket->user?->telegram_username)
                                <div class="text-xs text-gray-500">@ {{ $ticket->user->telegram_username }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match($ticket->status) {
                                    'open' => 'bg-yellow-500/15 text-yellow-300',
                                    'pending_user' => 'bg-green-500/15 text-green-300',
                                    'closed' => 'bg-gray-700 text-gray-400',
                                    default => 'bg-gray-700 text-gray-300',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $badge }}">
                                {{ $ticket->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $ticket->messages_count }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ optional($ticket->last_message_at ?? $ticket->created_at)->format('d.m.Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Тикетов нет.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tickets->hasPages())
        <div class="px-4 py-3 border-t border-gray-700">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
