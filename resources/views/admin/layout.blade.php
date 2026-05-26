<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - VPN Hub Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-gray-750 { background-color: #2d3748; }
    </style>
</head>
<body class="min-h-screen bg-gray-900">
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between min-h-16 py-2">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-white">
                        VPN Hub Admin
                    </a>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                Главная
                            </a>
                            <a href="{{ route('admin.settings') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                Настройки
                            </a>
                            <a href="{{ route('admin.trial-feedback') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.trial-feedback') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                Отзывы после теста
                            </a>
                            @php
                                $supportPending = \App\Models\SupportTicket::query()
                                    ->whereIn('status', ['open', 'pending_user'])
                                    ->count();
                            @endphp
                            <a href="{{ route('admin.support.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.support.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                Поддержка
                                @if($supportPending > 0)
                                    <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xs bg-red-600 text-white">{{ $supportPending }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
                <div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-300 hover:text-white text-sm">
                            Выйти
                        </button>
                    </form>
                </div>
            </div>
            <div class="md:hidden pb-4">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        Главная
                    </a>
                    <a href="{{ route('admin.settings') }}"
                       class="px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.settings') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        Настройки
                    </a>
                    <a href="{{ route('admin.trial-feedback') }}"
                       class="px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.trial-feedback') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        Отзывы после теста
                    </a>
                    <a href="{{ route('admin.support.index') }}"
                       class="px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.support.*') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        Поддержка
                        @if(($supportPending ?? 0) > 0)
                            <span class="ml-1 inline-flex items-center justify-center px-1.5 rounded-full text-xs bg-red-600 text-white">{{ $supportPending }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</body>
</html>
