<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TelegramLoginController extends Controller
{
    public function __construct(
        protected TelegramLoginService $telegramLoginService,
    ) {}

    /**
     * Telegram Login Widget callback.
     *
     * Telegram отправляет POST с полями:
     * id, first_name, username (optional), auth_date, hash, photo_url (optional).
     */
    public function callback(Request $request): RedirectResponse
    {
        $botToken = (string) config('services.telegram_bot.token');
        if ($botToken === '') {
            return redirect()
                ->route('login')
                ->withErrors(['telegram' => 'Telegram login is not configured.']);
        }

        $data = $request->all();

        $request->validate([
            'id' => ['required', 'integer'],
            'auth_date' => ['required', 'integer'],
            'hash' => ['required', 'string'],
            'username' => ['nullable', 'string', 'max:64'],
            'first_name' => ['nullable', 'string', 'max:128'],
            'last_name' => ['nullable', 'string', 'max:128'],
            'photo_url' => ['nullable', 'string', 'max:512'],
        ]);

        // Optional freshness check (protects against replay on leaked payloads).
        $authDate = (int) $request->input('auth_date');
        if ($authDate > 0 && (time() - $authDate) > 86400) {
            return redirect()
                ->route('login')
                ->withErrors(['telegram' => 'Telegram login payload expired.']);
        }

        if (! $this->telegramLoginService->verify($data, $botToken)) {
            return redirect()
                ->route('login')
                ->withErrors(['telegram' => 'Telegram login verification failed.']);
        }

        $tgId = (int) $request->input('id');
        $tgUsername = $request->input('username');

        $user = User::query()->where('telegram_id', $tgId)->first();
        if (! $user) {
            $placeholderEmail = 'tg-'.$tgId.'@bot.avavpn.ru';
            $name = is_string($tgUsername) && $tgUsername !== ''
                ? '@'.$tgUsername
                : ((string) ($request->input('first_name') ?: 'TG '.$tgId));

            $user = User::create([
                'name' => $name,
                'email' => $placeholderEmail,
                // Пользователь не должен входить по этому паролю — это технический пароль.
                'password' => Str::random(40),
                'telegram_id' => $tgId,
                'telegram_username' => is_string($tgUsername) ? $tgUsername : null,
            ]);
        } else {
            // Актуализируем username, если поменялся.
            if (is_string($tgUsername) && $tgUsername !== '' && $user->telegram_username !== $tgUsername) {
                $user->telegram_username = $tgUsername;
                $user->save();
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('cabinet.subscription'));
    }
}

