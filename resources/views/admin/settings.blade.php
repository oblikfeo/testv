@extends('admin.layout')

@section('title', 'Панели продаж')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">Активный сервер для платных подписок</h1>
    <p class="text-gray-400 mt-1">Новые ключи после оплаты создаются на выбранной панели.</p>
</div>

@if (session('success'))
    <div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 mb-6">
        <p class="text-green-400">{{ session('success') }}</p>
    </div>
@endif

<div class="bg-gray-800 rounded-xl p-6 mb-8">
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="space-y-4">
            @foreach($panels as $idx => $panel)
                <label class="flex items-start gap-3 p-4 rounded-lg border cursor-pointer {{ (int)$active === (int)$idx ? 'border-red-500 bg-red-500/10' : 'border-gray-600 hover:border-gray-500' }}">
                    <input type="radio" name="active_sale_panel" value="{{ $idx }}" class="mt-1" @checked((int)$active === (int)$idx)>
                    <div>
                        <div class="text-white font-medium">{{ \App\Support\HappSubscriptionFormatter::happNodeLabel((string) ($panel['happ_label'] ?? $panel['name'] ?? 'Сервер '.$idx)) }}</div>
                        <div class="text-gray-400 text-sm">{{ $panel['server_ip'] ?? '' }} — {{ $panel['url'] ?? '' }}</div>
                        <div class="text-xs mt-1 {{ ($health[$idx] ?? '') === 'ok' ? 'text-green-400' : 'text-yellow-400' }}">
                            Панель: {{ ($health[$idx] ?? '') === 'ok' ? 'доступна' : ($health[$idx] ?? 'не проверено') }}
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
        <button type="submit" class="mt-6 px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
            Сохранить
        </button>
    </form>
</div>
@endsection
