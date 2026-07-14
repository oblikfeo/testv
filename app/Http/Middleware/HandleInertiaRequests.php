<?php

namespace App\Http\Middleware;

use App\Models\TrialFeedback;
use App\Models\TrialFeedbackRequest;
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
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'telegram' => [
                'botUrl' => $this->telegramBotUrl(),
                'botUsername' => $this->telegramBotUsername(),
                'loginEnabled' => $this->telegramBotUsername() !== null && (string) config('services.telegram_bot.token') !== '',
                'channelUrl' => (string) config('app.telegram_channel_url') ?: null,
                'supportUrl' => (string) config('app.telegram_support_url') ?: null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'purchaseChoice' => fn () => $request->session()->get('purchase_choice'),
            'pendingTrialFeedback' => function () use ($user) {
                if (! $user) {
                    return false;
                }

                $alreadyLeftFeedback = TrialFeedback::query()
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyLeftFeedback) {
                    return false;
                }

                return TrialFeedbackRequest::query()
                    ->where('user_id', $user->id)
                    ->whereNull('submitted_at')
                    ->latest('id')
                    ->exists();
            },
        ];
    }

    private function telegramBotUsername(): ?string
    {
        $username = ltrim((string) (config('services.telegram_bot.username') ?? ''), '@');

        return $username !== '' ? $username : null;
    }

    private function telegramBotUrl(): ?string
    {
        $username = $this->telegramBotUsername();

        return $username !== null ? 'https://t.me/'.$username : null;
    }
}
