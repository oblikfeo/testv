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
                            <a href="{{ route('admin.test-keys') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.test-keys') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                Подписки
                            </a>
                            <a href="{{ route('admin.settings') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                Панели
                            </a>
                            <a href="{{ route('admin.sponsor') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.sponsor') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                2 сервера
                            </a>
                            <a href="{{ route('admin.admin-friends') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.admin-friends') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                Все серверы
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
                    <a href="{{ route('admin.test-keys') }}"
                       class="px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.test-keys') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        Подписки
                    </a>
                    <a href="{{ route('admin.settings') }}"
                       class="px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.settings') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        Панели
                    </a>
                    <a href="{{ route('admin.sponsor') }}"
                       class="px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.sponsor') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        2 сервера
                    </a>
                    <a href="{{ route('admin.admin-friends') }}"
                       class="col-span-2 px-3 py-2 rounded-md text-sm text-center font-medium {{ request()->routeIs('admin.admin-friends') ? 'bg-gray-900 text-white' : 'text-gray-300 bg-gray-700/60 hover:bg-gray-700 hover:text-white' }}">
                        Все серверы
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
