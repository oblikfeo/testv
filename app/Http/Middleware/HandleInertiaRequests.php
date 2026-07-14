<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'telegram' => [
                'botUrl' => $this->telegramBotUrl(),
                'channelUrl' => (string) config('app.telegram_channel_url') ?: null,
                'supportUrl' => (string) config('app.telegram_support_url') ?: null,
            ],
        ];
    }

    private function telegramBotUrl(): ?string
    {
        $username = (string) (config('services.telegram_bot.username') ?? '');

        return $username !== '' ? 'https://t.me/'.ltrim($username, '@') : null;
    }
}
