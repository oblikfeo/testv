<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Минимальный клиент к Telegram Bot API — нужен только чтобы пушить
 * пользователю сообщение «Оплата получена» после успешного webhook YooKassa.
 *
 * Если токен не сконфигурирован (TELEGRAM_BOT_TOKEN пуст) — молча не делает ничего.
 * Любые сетевые ошибки логируются, но не проваливают вебхук.
 */
class TelegramBotNotifier
{
    protected ?string $token;

    public function __construct()
    {
        $this->token = config('services.telegram_bot.token') ?: env('TELEGRAM_BOT_TOKEN');
    }

    public function isConfigured(): bool
    {
        return is_string($this->token) && $this->token !== '';
    }

    public function sendMessage(int $chatId, string $text, array $extra = []): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::asJson()
                ->timeout(8)
                ->post("https://api.telegram.org/bot{$this->token}/sendMessage", array_merge([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ], $extra));

            if (! $response->successful()) {
                Log::warning('TelegramBotNotifier: sendMessage failed', [
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('TelegramBotNotifier: exception', [
                'chat_id' => $chatId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
