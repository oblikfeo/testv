<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Мои ключи') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'key-issued')
                <p class="text-sm text-green-600 dark:text-green-400">{{ __('Ключ выдан.') }}</p>
            @endif
            @if (session('status') === 'key-activated')
                <p class="text-sm text-green-600 dark:text-green-400">{{ __('Подключение отмечено как активированное.') }}</p>
            @endif
            @error('issue')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <form method="POST" action="{{ route('keys.issue') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Получить ключ') }}
                    </button>
                </form>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('В продакшене выдача может быть привязана к оплате; сейчас это тестовая кнопка.') }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                    @forelse ($keys as $key)
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 last:pb-0">
                            <div class="flex flex-wrap gap-2 items-center justify-between">
                                <span class="text-sm font-medium">#{{ $key->id }} — {{ $key->pair?->name ?? '—' }}</span>
                                <span class="text-xs uppercase text-gray-500">{{ $key->status }}</span>
                            </div>
                            <p class="mt-2 text-xs break-all font-mono bg-gray-100 dark:bg-gray-900 p-2 rounded select-all" id="key-url-{{ $key->id }}">{{ $key->connection_url }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button type="button" class="text-xs text-indigo-600 dark:text-indigo-400" onclick="navigator.clipboard.writeText(document.getElementById('key-url-{{ $key->id }}').innerText)">
                                    {{ __('Копировать ссылку') }}
                                </button>
                                @if ($key->status === \App\Enums\SubscriptionKeyStatus::Issued->value)
                                    <form method="POST" action="{{ route('keys.activate', $key) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-600 dark:text-gray-400">{{ __('Отметить первый вход (тест)') }}</button>
                                    </form>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                @if ($key->issued_at)
                                    {{ __('Выдан:') }} {{ $key->issued_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Пока нет ключей.') }}</p>
                    @endforelse
                    {{ $keys->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
