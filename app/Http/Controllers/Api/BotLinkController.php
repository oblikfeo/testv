<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TelegramLinkCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BotLinkController extends Controller
{
    protected int $ttlMinutes = 10;

    /**
     * POST /api/bot/link/start
     * Body: telegram_id, telegram_username?, email
     */
    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:64',
            'telegram_first_name' => 'nullable|string|max:64',
            'telegram_last_name' => 'nullable|string|max:64',
            'email' => 'required|string|email|max:255',
        ]);

        $tgId = (int) $data['telegram_id'];
        $email = mb_strtolower(trim((string) $data['email']));

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return response()->json([
                'ok' => false,
                'error' => 'email_not_found',
                'message' => 'Пользователь с таким email не найден.',
            ], 404);
        }

        if ($user->telegram_id !== null && (int) $user->telegram_id !== $tgId) {
            return response()->json([
                'ok' => false,
                'error' => 'email_already_linked',
                'message' => 'Этот email уже привязан к другому Telegram.',
            ], 409);
        }

        $code = (string) random_int(100000, 999999);
        $cacheKey = $this->cacheKey($tgId, $email);

        Cache::put($cacheKey, [
            'code' => $code,
            'email' => $email,
            'telegram_id' => $tgId,
            'telegram_username' => $data['telegram_username'] ?? null,
            'telegram_first_name' => $data['telegram_first_name'] ?? null,
            'telegram_last_name' => $data['telegram_last_name'] ?? null,
        ], now()->addMinutes($this->ttlMinutes));

        Mail::to($email)->send(new TelegramLinkCodeMail($code, $this->ttlMinutes));

        return response()->json([
            'ok' => true,
            'message' => 'Код отправлен на email.',
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:64',
            'telegram_first_name' => 'nullable|string|max:64',
            'telegram_last_name' => 'nullable|string|max:64',
            'email' => 'required|string|email|max:255',
            'code' => 'required|string|min:6|max:6',
        ]);

        $tgId = (int) $data['telegram_id'];
        $email = mb_strtolower(trim((string) $data['email']));
        $code = trim((string) $data['code']);

        $cacheKey = $this->cacheKey($tgId, $email);
        $payload = Cache::get($cacheKey);
        if (! is_array($payload) || ($payload['code'] ?? null) !== $code) {
            return response()->json([
                'ok' => false,
                'error' => 'code_invalid',
                'message' => 'Код не подошёл или истёк.',
            ], 422);
        }

        $tgUsername = $data['telegram_username'] ?? ($payload['telegram_username'] ?? null);
        $tgFirstName = $data['telegram_first_name'] ?? ($payload['telegram_first_name'] ?? null);
        $tgLastName = $data['telegram_last_name'] ?? ($payload['telegram_last_name'] ?? null);

        try {
            $resultUser = DB::transaction(function () use ($tgId, $tgUsername, $tgFirstName, $tgLastName, $email): User {
                /** @var User $webUser */
                $webUser = User::query()->where('email', $email)->lockForUpdate()->firstOrFail();

                // If telegram is already linked to another user — stop.
                $existingTg = User::query()->where('telegram_id', $tgId)->lockForUpdate()->first();
                if ($existingTg && $existingTg->id !== $webUser->id) {
                    // If this is a bot-only user — merge its data into $webUser.
                    if (method_exists($existingTg, 'isBotOnly') && $existingTg->isBotOnly()) {
                        $this->mergeUsers($existingTg, $webUser);
                        $existingTg->delete();
                    } else {
                        throw new \RuntimeException('telegram_already_linked');
                    }
                }

                $webUser->telegram_id = $tgId;
                $webUser->telegram_username = is_string($tgUsername) ? $tgUsername : null;
                if (is_string($tgFirstName)) {
                    $webUser->telegram_first_name = $tgFirstName;
                }
                if (is_string($tgLastName)) {
                    $webUser->telegram_last_name = $tgLastName;
                }
                $webUser->save();

                return $webUser;
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'telegram_already_linked') {
                return response()->json([
                    'ok' => false,
                    'error' => 'telegram_already_linked',
                    'message' => 'Этот Telegram уже привязан к другому аккаунту.',
                ], 409);
            }
            throw $e;
        }

        Cache::forget($cacheKey);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $resultUser->id,
                'telegram_id' => $resultUser->telegram_id,
                'telegram_username' => $resultUser->telegram_username,
                'telegram_first_name' => $resultUser->telegram_first_name,
                'telegram_last_name' => $resultUser->telegram_last_name,
                'email' => $resultUser->email,
                'trial_used' => (bool) $resultUser->trial_used,
            ],
        ]);
    }

    protected function cacheKey(int $tgId, string $email): string
    {
        return 'bot_link_code:tg='.$tgId.':'.sha1($email);
    }

    protected function mergeUsers(User $from, User $to): void
    {
        // Re-assign ownership on all user-bound entities.
        DB::table('subscriptions')->where('user_id', $from->id)->update(['user_id' => $to->id]);
        DB::table('orders')->where('user_id', $from->id)->update(['user_id' => $to->id]);
        DB::table('trial_keys')->where('user_id', $from->id)->update(['user_id' => $to->id]);
        DB::table('support_tickets')->where('user_id', $from->id)->update(['user_id' => $to->id]);

        // Preserve trial_used if it was used in either account.
        if ($from->trial_used && ! $to->trial_used) {
            $to->trial_used = true;
            $to->save();
        }
    }
}

