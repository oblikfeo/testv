@extends('admin.layout')

@section('title', 'Спонсорские подписки')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">Спонсорские подписки</h1>
    <p class="text-gray-400 mt-1">Два соединения (NL + FR) в одной подписке Happ. Осталось слотов: <strong class="text-white">{{ $remaining }}</strong> / 10</p>
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

<div class="bg-gray-800 rounded-xl p-6 max-w-xl">
    <form method="POST" action="{{ route('admin.sponsor.create') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Email пользователя (зарегистрирован в ЛК)</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Срок (дней)</label>
            <input type="number" name="days" value="{{ old('days', 30) }}" min="1" max="3650" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Трафик (GB), 0 = без лимита</label>
            <input type="number" name="traffic_gb" value="{{ old('traffic_gb', 0) }}" min="0" max="100000" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-1">Макс. устройств (HWID)</label>
            <input type="number" name="max_devices" value="{{ old('max_devices', 5) }}" min="1" max="50" required
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
        </div>
        <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition" @if($remaining <= 0) disabled @endif>
            Выдать спонсорскую подписку
        </button>
    </form>
</div>
@endsection
