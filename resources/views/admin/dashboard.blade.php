@extends('admin.layout')

@section('title', 'Главная')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <a href="{{ route('admin.test-keys') }}" 
       class="bg-gray-800 rounded-xl p-6 hover:bg-gray-750 transition group">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Тестовые ключи</h3>
                <p class="text-gray-400 text-sm mt-1">Управление тестовыми VPN ключами</p>
            </div>
            <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center group-hover:bg-blue-500/20 transition">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
        </div>
    </a>

    <div class="bg-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Связка 1 (NL)</h3>
                <p class="text-gray-400 text-sm mt-1">158.160.229.195</p>
                <p class="text-green-400 text-xs mt-2">● Для продажи</p>
            </div>
            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Связка 2 (FR)</h3>
                <p class="text-gray-400 text-sm mt-1">158.160.249.138</p>
                <p class="text-green-400 text-xs mt-2">● Для продажи</p>
            </div>
            <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-800 rounded-xl p-6">
    <h2 class="text-xl font-bold text-white mb-4">Архитектура VPN Hub</h2>
    <div class="text-gray-400 space-y-2 text-sm">
        <p><strong class="text-white">Связка 1:</strong> Yandex 158.160.229.195 → NL 82.23.162.45 (6 GB RAM, 118 GB диск)</p>
        <p><strong class="text-white">Связка 2:</strong> Yandex 158.160.249.138 → FR 82.22.50.114 (6 GB RAM, 118 GB диск)</p>
        <p><strong class="text-yellow-400">Связка 3 (тестовая):</strong> Yandex 158.160.253.217 → NL 82.23.163.202 (2 GB RAM, 58 GB диск)</p>
    </div>
</div>
@endsection
