@extends('admin.layout')

@section('title', 'Тестовые ключи')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">Тестовые ключи</h1>
    <p class="text-gray-400 mt-1">Связка 3: {{ $testPanel['url'] }} (2 GB RAM, слабый сервер)</p>
</div>

@if (session('success'))
    <div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-400">{{ session('success') }}</p>
        @if (session('vless_link'))
            <div class="mt-3">
                <label class="block text-sm text-gray-400 mb-1">VLESS ссылка:</label>
                <div class="flex gap-2">
                    <input type="text" 
                           value="{{ session('vless_link') }}" 
                           readonly
                           id="vless-link"
                           class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm font-mono">
                    <button onclick="navigator.clipboard.writeText(document.getElementById('vless-link').value)"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
                        Копировать
                    </button>
                </div>
            </div>
        @endif
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 mb-6">
        <p class="text-red-400">{{ $errors->first() }}</p>
    </div>
@endif

@if ($error)
    <div class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 mb-6">
        <p class="text-red-400">Ошибка подключения к панели: {{ $error }}</p>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Создать тестовый ключ</h2>
        <form method="POST" action="{{ route('admin.test-keys.create') }}" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm text-gray-400 mb-1">Время жизни (часы)</label>
                <input type="number" 
                       name="hours" 
                       value="8" 
                       min="1" 
                       max="24"
                       class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white w-24">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Лимит трафика (GB)</label>
                <input type="number" 
                       name="gb" 
                       value="10" 
                       min="1" 
                       max="50"
                       class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white w-24">
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                Создать ключ
            </button>
        </form>
        <p class="text-gray-500 text-sm mt-3">По умолчанию: 8 часов, 10 GB — после истечения ключ автоматически станет неактивным</p>
    </div>

    <div class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-2">Статистика</h2>
        <div class="text-3xl font-bold text-blue-400">{{ count($clients) }}</div>
        <p class="text-gray-400 text-sm">активных тестовых ключей</p>
    </div>
</div>

<div class="bg-gray-800 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-700">
        <h2 class="text-lg font-semibold text-white">Список тестовых ключей</h2>
    </div>
    
    @if (empty($clients))
        <div class="p-6 text-center text-gray-400">
            Нет тестовых ключей
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Пользователь ЛК</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Трафик</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Лимит</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Истекает</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($clients as $client)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-mono text-sm">{{ $client['email'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300 max-w-xs">
                                @if(isset($trialByEmail[$client['email']]))
                                    <span class="text-white break-all">{{ $trialByEmail[$client['email']]->user?->email ?? '—' }}</span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($client['enable'])
                                    <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded-full">Активен</span>
                                @else
                                    <span class="px-2 py-1 bg-red-500/10 text-red-400 text-xs rounded-full">Отключён</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                {{ number_format(($client['up'] + $client['down']) / 1024 / 1024 / 1024, 2) }} GB
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                @if ($client['total_gb'] > 0)
                                    {{ number_format($client['total_gb'] / 1024 / 1024 / 1024, 0) }} GB
                                @else
                                    ∞
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                @if ($client['expiry_time'] > 0)
                                    @php
                                        $expiry = \Carbon\Carbon::createFromTimestampMs($client['expiry_time']);
                                        $isExpired = $expiry->isPast();
                                    @endphp
                                    <span class="{{ $isExpired ? 'text-red-400' : '' }}">
                                        {{ $expiry->format('d.m.Y H:i') }}
                                    </span>
                                @else
                                    Бессрочно
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.test-keys.delete') }}" class="inline"
                                      onsubmit="return confirm('Удалить ключ {{ $client['email'] }}?')">
                                    @csrf
                                    <input type="hidden" name="inbound_id" value="{{ $client['inbound_id'] }}">
                                    <input type="hidden" name="uuid" value="{{ $client['uuid'] }}">
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
