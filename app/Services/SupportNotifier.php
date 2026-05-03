<?php

namespace App\Services;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Уведомления по тикетам поддержки.
 * - Админу: пуш в Telegram-чат (SUPPORT_ADMIN_TELEGRAM_CHAT_ID).
 * - Пользователю: пуш в Telegram (если привязан) + простое письмо.
 *
 * Все каналы — best-effort, ошибки логируются и не валят запрос.
 */
class SupportNotifier
{
    public function __construct(
        protected TelegramBotNotifier $telegram
    ) {}

    public function notifyAdminNewTicket(SupportTicket $ticket, SupportMessage $firstMessage): void
    {
        $chatId = $this->adminChatId();
        if ($chatId === null) {
            return;
        }

        $url = url('/admin/support/'.$ticket->id);
        $user = $ticket->user;
        $userLine = $user ? sprintf('%s (id %d)', $user->email ?? '—', $user->id) : '—';

        $text = "🆕 <b>Новый тикет #{$ticket->id}</b>\n"
            ."Категория: ".e($ticket->categoryLabel())."\n"
            ."Тема: ".e($ticket->subject)."\n"
            ."Пользователь: ".e($userLine)."\n\n"
            .e(mb_strimwidth($firstMessage->body, 0, 600, '…'))."\n\n"
            .$url;

        $this->telegram->sendMessage($chatId, $text);
    }

    public function notifyAdminUserReply(SupportTicket $ticket, SupportMessage $message): void
    {
        $chatId = $this->adminChatId();
        if ($chatId === null) {
            return;
        }

        $url = url('/admin/support/'.$ticket->id);
        $text = "💬 <b>Ответ в тикете #{$ticket->id}</b>\n"
            ."Тема: ".e($ticket->subject)."\n\n"
            .e(mb_strimwidth($message->body, 0, 600, '…'))."\n\n"
            .$url;

        $this->telegram->sendMessage($chatId, $text);
    }

    public function notifyUserAdminReply(SupportTicket $ticket, SupportMessage $message): void
    {
        $user = $ticket->user;
        if (! $user) {
            return;
        }

        $url = url('/cabinet/support/'.$ticket->id);

        if ($user->telegram_id) {
            $tgText = "📩 <b>Ответ в тикете #{$ticket->id}</b>\n"
                ."Тема: ".e($ticket->subject)."\n\n"
                .e(mb_strimwidth($message->body, 0, 600, '…'))."\n\n"
                .$url;
            $this->telegram->sendMessage((int) $user->telegram_id, $tgText);
        }

        if ($user->email && ! str_ends_with((string) $user->email, '@bot.avavpn.ru')) {
            try {
                $email = $user->email;
                $subject = 'Ответ поддержки — тикет #'.$ticket->id;
                $body = "Здравствуйте!\n\n"
                    ."По вашему обращению «{$ticket->subject}» поступил ответ:\n\n"
                    .$message->body."\n\n"
                    ."Открыть переписку: {$url}\n\n"
                    .'— Команда AVA VPN';

                Mail::raw($body, function ($m) use ($email, $subject) {
                    $m->to($email)->subject($subject);
                });
            } catch (\Throwable $e) {
                Log::warning('SupportNotifier: mail failed', [
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function adminChatId(): ?int
    {
        $raw = (string) config('support.admin_telegram_chat_id');
        if ($raw === '') {
            return null;
        }

        return (int) $raw;
    }
}
