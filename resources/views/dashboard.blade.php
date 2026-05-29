<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-2">
                    <p>{{ __("You're logged in!") }}</p>
                    <a href="{{ route('cabinet.subscription') }}" class="text-indigo-600 dark:text-indigo-400 text-sm underline">{{ __('Перейти к моим ключам') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
