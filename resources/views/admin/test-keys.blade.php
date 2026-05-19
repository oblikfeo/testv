@extends('admin.layout')

@section('title', 'Подписки')

@section('content')
@php
    $defaultHours = (int) ($trialDefaults['hours'] ?? config('admin.trial.duration_hours', 3));
    $defaultGb = (int) ($trialDefaults['gb'] ?? config('admin.trial.soft_quota_gb', 5));
@endphp

<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">Подписки</h1>
    <p class="text-gray-400 mt-1">Тест-драйв: общая подписка Happ (как в кабинете), без панели 3x-ui</p>
</div>

@if (session('success'))
    <div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-400">{{ session('success') }}</p>
        @if (session('vless_link'))
            <div class="mt-3">
                <label class="block text-sm text-gray-400 mb-1">Ссылка подписки:</label>
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
            <h2 class="text-lg font-semibold text-white mb-4">Выдать тест-драйв пользователю</h2>
            <form method="POST" action="{{ route('admin.test-keys.create') }}" class="flex flex-col sm:flex-row sm:flex-wrap gap-4 sm:items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm text-gray-400 mb-1">Email пользователя</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@example.com"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Время жизни (часы)</label>
                    <input type="number" 
                           name="hours" 
                           value="{{ old('hours', $defaultHours) }}" 
                           min="1" 
                           max="24"
                           class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white w-24">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Лимит трафика (GB)</label>
                    <input type="number" 
                           name="gb" 
                           value="{{ old('gb', $defaultGb) }}" 
                           min="0" 
                           max="50"
                           class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white w-24">
                </div>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition w-full sm:w-auto">
                    Выдать доступ
                </button>
            </form>
            <p class="text-gray-500 text-sm mt-3">По умолчанию: {{ (int) config('admin.trial.duration_hours', 3) }} ч., {{ (int) config('admin.trial.soft_quota_gb', 5) }} ГБ — после истечения ключ автоматически станет неактивным</p>
        </div>

        <div class="bg-gray-800 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-2">Статистика</h2>
            <div class="text-3xl font-bold text-blue-400">{{ $trialKeys->count() }}</div>
            <p class="text-gray-400 text-sm">тестовых ключей</p>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white">Список тестовых ключей</h2>
        </div>

        @if ($trialKeys->isEmpty())
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
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Подписка</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Статус</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Трафик</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Лимит</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Истекает</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($trialKeys as $trialKey)
                            @php
                                $user = $trialKey->user;
                                $tgLabel = $user?->telegramDisplayLabel();
                                $tgLink = $user?->telegramDeeplink();
                                $emailValue = $user?->email ?? '—';
                                $subUrl = url('/sub/'.$trialKey->sub_id);
                            @endphp
                            <tr class="hover:bg-gray-700/30">
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-200">
                                    @if ($tgLabel && $tgLink)
                                        <a href="{{ $tgLink }}" target="_blank" rel="noopener" class="text-blue-300 hover:text-blue-200 hover:underline">{{ $tgLabel }}</a>
                                    @elseif ($tgLabel)
                                        {{ $tgLabel }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ $emailValue }}</td>
                                <td class="px-3 sm:px-6 py-4 text-sm">
                                    <a href="{{ $subUrl }}" target="_blank" rel="noopener" class="text-blue-300 hover:underline font-mono text-xs break-all">{{ $subUrl }}</a>
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    @if ($trialKey->isExpired())
                                        <span class="px-2 py-1 bg-red-500/10 text-red-400 text-xs rounded-full">Истёк</span>
                                    @elseif ($trialKey->isTrafficExceeded())
                                        <span class="px-2 py-1 bg-orange-500/10 text-orange-400 text-xs rounded-full">Лимит исчерпан</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-500/10 text-green-400 text-xs rounded-full">Активен</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                    {{ $trialKey->getUsedGb() }} GB
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                    @if ($trialKey->total_bytes > 0)
                                        {{ $trialKey->getTotalGb() }} GB
                                    @else
                                        ∞
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-gray-300 text-sm">
                                    {{ $trialKey->expires_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.test-keys.delete') }}" class="inline"
                                          onsubmit="return confirm('Отозвать тестовый доступ?')">
                                        @csrf
                                        <input type="hidden" name="trial_key_id" value="{{ $trialKey->id }}">
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                            Отозвать
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
    <div class="bg-gray-800 rounded-xl p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('admin.test-keys') }}" class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-3">
            <input type="hidden" name="tab" value="paid">

            <label class="text-sm text-gray-300">
                Источник
                <select name="source" class="mt-1 w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">Все</option>
                    <option value="web" @selected(($paidFilters['source'] ?? '') === 'web')>Сайт</option>
                    <option value="bot" @selected(($paidFilters['source'] ?? '') === 'bot')>Бот</option>
                    <option value="unknown" @selected(($paidFilters['source'] ?? '') === 'unknown')>Неизвестно</option>
                </select>
            </label>

            <label class="text-sm text-gray-300">
                Статус
                <select name="status" class="mt-1 w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">Все</option>
                    <option value="active" @selected(($paidFilters['status'] ?? '') === 'active')>Активен</option>
                    <option value="expired" @selected(($paidFilters['status'] ?? '') === 'expired')>Истёк</option>
                    <option value="limit_exceeded" @selected(($paidFilters['status'] ?? '') === 'limit_exceeded')>Лимит исчерпан</option>
                    <option value="sub_inactive" @selected(($paidFilters['status'] ?? '') === 'sub_inactive')>Подписка неактивна</option>
                    <option value="revoked" @selected(($paidFilters['status'] ?? '') === 'revoked')>Отозван</option>
                </select>
            </label>

            <label class="text-sm text-gray-300">
                Трафик
                <select name="traffic" class="mt-1 w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">Любой</option>
                    <option value="remaining" @selected(($paidFilters['traffic'] ?? '') === 'remaining')>Есть остаток</option>
                    <option value="exhausted" @selected(($paidFilters['traffic'] ?? '') === 'exhausted')>Исчерпан</option>
                    <option value="unlimited" @selected(($paidFilters['traffic'] ?? '') === 'unlimited')>Безлимит</option>
                </select>
            </label>

            <label class="text-sm text-gray-300">
                Истекает
                <select name="expiring" class="mt-1 w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">Любой срок</option>
                    <option value="today" @selected(($paidFilters['expiring'] ?? '') === 'today')>Сегодня</option>
                    <option value="3days" @selected(($paidFilters['expiring'] ?? '') === '3days')>В ближайшие 3 дня</option>
                    <option value="7days" @selected(($paidFilters['expiring'] ?? '') === '7days')>В ближайшие 7 дней</option>
                    <option value="expired" @selected(($paidFilters['expiring'] ?? '') === 'expired')>Уже истёк</option>
                </select>
            </label>

            <label class="text-sm text-gray-300">
                Сортировать по
                <select name="sort_by" class="mt-1 w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="expires_at" @selected(($paidFilters['sort_by'] ?? 'expires_at') === 'expires_at')>Истечению</option>
                    <option value="traffic" @selected(($paidFilters['sort_by'] ?? '') === 'traffic')>Трафику</option>
                    <option value="source" @selected(($paidFilters['sort_by'] ?? '') === 'source')>Источнику</option>
                    <option value="status" @selected(($paidFilters['sort_by'] ?? '') === 'status')>Статусу</option>
                </select>
            </label>

            <label class="text-sm text-gray-300">
                Порядок
                <select name="sort_dir" class="mt-1 w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="desc" @selected(($paidFilters['sort_dir'] ?? 'desc') === 'desc')>По убыванию</option>
                    <option value="asc" @selected(($paidFilters['sort_dir'] ?? '') === 'asc')>По возрастанию</option>
                </select>
            </label>

            <div class="md:col-span-3 xl:col-span-6 flex gap-3 mt-1">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
                    Применить
                </button>
                <a href="{{ route('admin.test-keys', ['tab' => 'paid']) }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg text-sm transition">
                    Сбросить
                </a>
            </div>
        </form>
    </div>

    <div class="bg-gray-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white">Список оплаченных ключей</h2>
            <p class="text-xs text-gray-400 mt-1">Найдено: {{ $paidKeys->count() }}</p>
        </div>

        @if ($paidKeys->isEmpty())
            <div class="p-6 text-center text-gray-400">
                Оплаченных ключей пока нет
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1180px]">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">TG ник</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
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
                                $tgLabel = $saleKey->user?->telegramDisplayLabel();
                                $tgLink = $saleKey->user?->telegramDeeplink();
                                $emailValue = $saleKey->user?->email ?: ($saleKey->email ?: '—');
                                $source = $saleKey->subscription?->purchase_source ?: ($saleKey->keyOrder?->purchase_source ?: 'unknown');
                                $isExpired = $saleKey->expires_at?->isPast() ?? false;
                                $isLimitExceeded = $saleKey->total_bytes > 0 && $saleKey->used_bytes >= $saleKey->total_bytes;
                                $isSubscriptionInactive = $saleKey->subscription && $saleKey->subscription->status !== 'active';
                            @endphp
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm">
                                @if ($tgLabel && $tgLink)
                                    <a href="{{ $tgLink }}" target="_blank" rel="noopener" class="text-blue-300 hover:text-blue-200 hover:underline">{{ $tgLabel }}</a>
                                @elseif ($tgLabel)
                                    <span class="text-gray-200">{{ $tgLabel }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $emailValue }}
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
