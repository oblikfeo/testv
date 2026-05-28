@extends('admin.layout')

@section('title', 'Триалы')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">Тестовые периоды</h1>
    <p class="text-gray-400 text-sm mt-1">Узлы подписки ({{ count(\App\Support\SharedVpnAccess::nodeUris()) }}): @foreach(\App\Support\SharedVpnAccess::nodeUris() as $uri)<code class="text-gray-300 block mt-1 break-all">{{ $uri }}</code>@endforeach</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-900/40 border border-green-700 rounded-lg text-green-300">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 p-4 bg-red-900/40 border border-red-700 rounded-lg text-red-300">{{ $errors->first() }}</div>
@endif

<div class="bg-gray-800 rounded-xl p-6 mb-8">
    <h2 class="text-lg font-semibold text-white mb-4">Выдать триал</h2>
    <form method="POST" action="{{ route('admin.trials.create') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        @csrf
        <div>
            <label class="block text-sm text-gray-400 mb-1">Email пользователя</label>
            <input type="email" name="email" required class="w-full rounded-lg bg-gray-900 border-gray-700 text-white px-3 py-2" value="{{ old('email') }}">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Часов</label>
            <input type="number" name="hours" min="1" max="168" value="{{ old('hours', $trialDefaults['hours']) }}" class="w-full rounded-lg bg-gray-900 border-gray-700 text-white px-3 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Soft-лимит ГБ (0 = без отображения)</label>
            <input type="number" name="gb" min="0" max="50" value="{{ old('gb', $trialDefaults['gb']) }}" class="w-full rounded-lg bg-gray-900 border-gray-700 text-white px-3 py-2">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg">Выдать</button>
    </form>
</div>

<div class="bg-gray-800 rounded-xl overflow-hidden">
    <table class="min-w-full text-sm text-left text-gray-300">
        <thead class="bg-gray-900 text-gray-400 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Пользователь</th>
                <th class="px-4 py-3">Истекает</th>
                <th class="px-4 py-3">Статус</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($trialKeys as $key)
                <tr class="border-t border-gray-700">
                    <td class="px-4 py-3">{{ $key->id }}</td>
                    <td class="px-4 py-3">{{ $key->user?->email ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $key->expires_at?->format('d.m.Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $key->isActive() ? 'активен' : 'истёк' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.trials.revoke') }}" onsubmit="return confirm('Отозвать триал?')">
                            @csrf
                            <input type="hidden" name="trial_key_id" value="{{ $key->id }}">
                            <button type="submit" class="text-red-400 hover:text-red-300">Отозвать</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Нет записей</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
