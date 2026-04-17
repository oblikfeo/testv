<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - Вход</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-gray-800 rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">VPN Hub Admin</h1>
                <p class="text-gray-400 mt-2">Вход в панель управления</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 mb-6">
                    <p class="text-red-400 text-sm">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.authenticate') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-300 mb-2">Логин</label>
                    <input type="text" 
                           name="login" 
                           id="login"
                           required
                           autofocus
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Пароль</label>
                    <input type="password" 
                           name="password" 
                           id="password"
                           required
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                    Войти
                </button>
            </form>
        </div>
    </div>
</body>
</html>
