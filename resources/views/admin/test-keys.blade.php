@extends('admin.layout')

@section('title', 'Подписки')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">Подписки</h1>
    <p class="text-gray-400 mt-1">Тестовая панель: {{ $testPanel['url'] }} (2 GB RAM)</p>
</div>

@if (session('success'))
    <div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-400">{{ session('success') }}</p>
        @if (session('vless_link'))
            <div class="mt-3">
                <label class="block text-sm text-gray-400 mb-1">VLESS ссылка:</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" 
                           value="{{ session('vless_link') }}" 
                           readonly
                           id="vless-link"
                           class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm font-mono">
                    <button onclick="navigator.clipboard.writeText(document.getElementById('vless-link').value)"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition sm:w-auto w-full">
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

<div class="mb-6">
    <div class="bg-gray-800 rounded-xl p-2 inline-flex gap-2">
        <a href="{{ route('admin.test-keys', ['tab' => 'test']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $activeTab === 'paid' ? 'text-gray-300 hover:bg-gray-700' : 'bg-blue-600 text-white' }}">
            Тестовые ключи
        </a>
        <a href="{{ route('admin.test-keys', ['tab' => 'paid']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $activeTab === 'paid' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
            Оплаченные ключи
        </a>
    </div>
</div>

@if ($activeTab !== 'paid')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Создать тестовый ключ</h2>
            <form method="POST" action="{{ route('admin.test-keys.create') }}" class="flex flex-col sm:flex-row sm:flex-wrap gap-4 sm:items-end">
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
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition w-full sm:w-auto">
                    Создать ключ
                </button>
            </form>
            <p class="text-gray-500 text-sm mt-3">По умолчанию: 8 часов, 10 GB — после истечения ключ автоматически станет неактивным</p>
        </div>

        <div class="bg-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-2">Статистика</h2>
            <div class="text-3xl font-bold text-blue-400">{{ count($clients) }}</div>
            <p class="text-gray-400 text-sm">тестовых ключей</p>
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
                <table class="w-full min-w-[1180px]">
                    <thead class="bg-gray-700/50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">TG ник</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Статус</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Трафик</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Лимит</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Истекает</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($clients as $client)
                            @php
                                $trial = $trialByEmail[$client['email']] ?? null;
                                $user = $trial?->user;
                                $tgUsername = $user?->telegram_username ? '@'.$user->telegram_username : '—';
                                $emailValue = $user?->email ?: ($client['email'] ?: '—');
                                $usageBytes = (int) ($client['up'] + $client['down']);
                                $limitBytes = (int) $client['total_gb'];
                                $expiry = $client['expiry_time'] > 0 ? \Carbon\Carbon::createFromTimestampMs($client['expiry_time']) : null;
                                $isExpired = $expiry?->isPast() ?? false;
                                $isLimitExceeded = $limitBytes > 0 && $usageBytes >= $limitBytes;
                            @endphp
                            <tr class="hover:bg-gray-700/30">
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ $tgUsername }}</td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ $emailValue }}</td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    @if (! $client['enable'])
                                        <span class="px-2 py-1 bg-gray-500/10 text-gray-300 text-xs rounded-full">Отключён</span>
                                    @elseif ($isExpired)
                                        <span class="px-2 py-1 bg-red-500/10 text-red-400 text-xs rounded-full">Истёк</span>
                                    @elseif ($isLimitExceeded)
                                        <span class="px-2 py-1 bg-orange-500/10 text-orange-400 text-xs rounded-full">Лимит исчерпан</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded-full">Активен</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                    {{ number_format($usageBytes / 1024 / 1024 / 1024, 2) }} GB
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                    @if ($limitBytes > 0)
                                        {{ number_format($limitBytes / 1024 / 1024 / 1024, 0) }} GB
                                    @else
                                        ∞
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                    {{ $expiry ? $expiry->format('d.m.Y H:i') : 'Бессрочно' }}
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
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
@else
    <div class="bg-gray-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white">Список оплаченных ключей</h2>
        </div>

        @if ($paidKeys->isEmpty())
            <div class="p-6 text-center text-gray-400">
                Оплаченных ключей пока нет
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1300px]">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">TG ник</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Тариф</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Источник</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Статус</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Трафик</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Лимит</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Истекает</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($paidKeys as $saleKey)
                        <tr class="hover:bg-gray-700/30">
                            @php
                                $tgUsername = $saleKey->user?->telegram_username ? '@'.$saleKey->user->telegram_username : '—';
                                $emailValue = $saleKey->user?->email ?: ($saleKey->email ?: '—');
                                $planLabel = $saleKey->subscription?->plan?->name ?: '—';
                                $source = $saleKey->subscription?->purchase_source ?: ($saleKey->keyOrder?->purchase_source ?: 'unknown');
                                $isExpired = $saleKey->expires_at?->isPast() ?? false;
                                $isLimitExceeded = $saleKey->total_bytes > 0 && $saleKey->used_bytes >= $saleKey->total_bytes;
                                $isSubscriptionInactive = $saleKey->subscription && $saleKey->subscription->status !== 'active';
                            @endphp
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-200 text-sm">{{ $tgUsername }}</span>
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $emailValue }}
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $planLabel }}
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                @if ($source === 'bot')
                                    <span class="px-2 py-1 bg-indigo-500/10 text-indigo-300 text-xs rounded-full">Бот</span>
                                @elseif ($source === 'web')
                                    <span class="px-2 py-1 bg-cyan-500/10 text-cyan-300 text-xs rounded-full">Сайт</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-500/10 text-gray-300 text-xs rounded-full">Неизвестно</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                @if ($saleKey->status !== 'active')
                                    <span class="px-2 py-1 bg-gray-500/10 text-gray-300 text-xs rounded-full">Отозван</span>
                                @elseif ($isSubscriptionInactive)
                                    <span class="px-2 py-1 bg-yellow-500/10 text-yellow-300 text-xs rounded-full">Подписка неактивна</span>
                                @elseif ($isExpired)
                                    <span class="px-2 py-1 bg-red-500/10 text-red-400 text-xs rounded-full">Истёк</span>
                                @elseif ($isLimitExceeded)
                                    <span class="px-2 py-1 bg-orange-500/10 text-orange-400 text-xs rounded-full">Лимит исчерпан</span>
                                @else
                                    <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded-full">Активен</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                {{ number_format(($saleKey->used_bytes ?? 0) / 1024 / 1024 / 1024, 2) }} GB
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                @if (($saleKey->total_bytes ?? 0) > 0)
                                    {{ number_format($saleKey->total_bytes / 1024 / 1024 / 1024, 0) }} GB
                                @else
                                    ∞
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                {{ $saleKey->expires_at?->format('d.m.Y H:i') ?? '—' }}
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.paid-keys.delete') }}" class="inline"
                                      onsubmit="return confirm('Удалить оплаченный ключ {{ $saleKey->email }}?')">
                                    @csrf
                                    <input type="hidden" name="sale_key_id" value="{{ $saleKey->id }}">
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
@endif
@endsection
