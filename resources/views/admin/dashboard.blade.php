@extends('admin.layout')

@section('title', 'Главная')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <a href="{{ route('admin.trials') }}"
       class="bg-gray-800 rounded-xl p-6 hover:bg-gray-750 transition group">
        <h3 class="text-lg font-semibold text-white">Триалы</h3>
        <p class="text-gray-400 text-sm mt-1">Выдача и отзыв тестового доступа</p>
    </a>

    <a href="{{ route('admin.trial-feedback') }}"
       class="bg-gray-800 rounded-xl p-6 hover:bg-gray-750 transition group">
        <h3 class="text-lg font-semibold text-white">Отзывы после теста</h3>
        <p class="text-gray-400 text-sm mt-1">Обратная связь от пользователей триала</p>
    </a>

    <a href="{{ route('admin.support.index') }}"
       class="bg-gray-800 rounded-xl p-6 hover:bg-gray-750 transition group">
        <h3 class="text-lg font-semibold text-white">Поддержка</h3>
        <p class="text-gray-400 text-sm mt-1">Тикеты пользователей</p>
    </a>
</div>

<div class="bg-gray-800 rounded-xl p-6">
    <h2 class="text-xl font-bold text-white mb-4">Подключение пользователей</h2>
    <div class="text-gray-400 space-y-2 text-sm">
        <p>Все активные подписки и триал используют одну Hysteria2-ссылку из <code class="text-gray-300">SHARED_HY2_URI</code>.</p>
        <p>Срок доступа контролируется в БД (таблицы <code class="text-gray-300">subscriptions</code> и <code class="text-gray-300">trial_keys</code>).</p>
        <p>Новых пользователей в Blitz при оплате не создаём.</p>
    </div>
</div>
@endsection
