@extends('admin.layout')

@section('title', 'Полный доступ')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">Полный доступ (все серверы продаж)</h1>
    <p class="text-gray-400 mt-1 max-w-2xl">
        Все серверы из <code class="text-gray-300">sale_panels</code> — одна ссылка Happ, внутри отдельная VLESS на каждую страну.
        Глобально может быть только <strong class="text-white">одна</strong> такая подписка.
    </p>
</div>

@if (session('success'))
    <div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-400">{{ session('success') }}</p>
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 mb-6">
        <p class="text-red-400">{{ $errors->first() }}</p>
    </div>
@endif

@if ($adminKey)
    <div class="bg-gray-800 rounded-xl p-6 mb-8 border border-yellow-500/30">
        <h2 class="text-lg font-semibold text-white mb-2">Сейчас выдана</h2>
        <p class="text-gray-300 text-sm mb-1">Пользователь: <strong class="text-white">{{ $adminKey->user->email ?? '—' }}</strong> (id {{ $adminKey->user_id }})</p>
        <p class="text-gray-300 text-sm mb-4">До: {{ $adminKey->expires_at->format('d.m.Y H:i') }}</p>
        <div class="flex flex-wrap gap-3 items-center">
            <input type="text" readonly value="{{ url('/sub/'.$adminKey->sub_id) }}" id="admin-sub-url"
                   class="flex-1 min-w-[280px] px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm font-mono">
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('admin-sub-url').value)"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">Копировать</button>
        </div>
        <form method="POST" action="{{ route('admin.admin-friends.revoke') }}" class="mt-6" onsubmit="return confirm('Удалить всех клиентов с панелей и подписку?');">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600/80 hover:bg-red-600 text-white rounded-lg text-sm">
                Отозвать и освободить слот
            </button>
        </form>
    </div>
@endif

<div class="bg-gray-800 rounded-xl p-6 max-w-xl {{ $adminKey ? 'opacity-60 pointer-events-none' : '' }}">
    <h2 class="text-lg font-semibold text-white mb-4">{{ $adminKey ? 'Сначала отзовите текущую' : 'Выдать подписку' }}</h2>
    <form method="POST" action="{{ route('admin.admin-friends.create') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Email пользователя ЛК</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white" @disabled($adminKey)>
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Срок (дней)</label>
            <input type="number" name="days" value="{{ old('days', 365) }}" min="1" max="3650" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white" @disabled($adminKey)>
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Трафик (GB), 0 = без лимита</label>
            <input type="number" name="traffic_gb" value="{{ old('traffic_gb', 0) }}" min="0" max="100000" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white" @disabled($adminKey)>
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Макс. устройств (HWID)</label>
            <input type="number" name="max_devices" value="{{ old('max_devices', 10) }}" min="1" max="50" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white" @disabled($adminKey)>
        </div>
        <button type="submit" class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition" @disabled($adminKey)>
            Создать подписку «все серверы»
        </button>
    </form>
</div>
@endsection
