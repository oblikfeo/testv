<?php

namespace App\Console\Commands;

use App\Models\TrialFeedback;
use App\Models\TrialFeedbackRequest;
use App\Models\TrialKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyTrialFeedbackRequestsCommand extends Command
{
    protected $signature = 'trial-feedback:notify';

    protected $description = 'Создаёт запросы отзыва после завершения триала и шлёт email пользователям без Telegram';

    public function handle(): int
    {
        $created = 0;
        $emails = 0;

        TrialKey::query()
            ->with('user')
            ->each(function (TrialKey $trialKey) use (&$created, &$emails): void {
                $user = $trialKey->user;
                if (! $user) {
                    return;
                }

                $trialEnded = $trialKey->expires_at->isPast() || $trialKey->isTrafficExceeded();
                if (! $trialEnded) {
                    return;
                }

                $feedbackExists = TrialFeedback::query()
                    ->where('user_id', $user->id)
                    ->exists();
                if ($feedbackExists) {
                    TrialFeedbackRequest::query()
                        ->where('user_id', $user->id)
                        ->whereNull('submitted_at')
                        ->update(['submitted_at' => now()]);

                    return;
                }

                $trigger = $trialKey->isTrafficExceeded() ? 'trial_traffic_exhausted' : 'trial_expired';
                $request = TrialFeedbackRequest::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'trial_key_id' => $trialKey->id,
                        'trigger' => $trigger,
                    ]
                );
                if ($request->wasRecentlyCreated) {
                    $created++;
                }

                if ($user->telegram_id || $request->email_sent_at !== null) {
                    return;
                }

                $email = (string) ($user->email ?? '');
                if ($email === '' || str_ends_with($email, '@bot.avavpn.ru')) {
                    return;
                }

                try {
                    $subject = 'AVA VPN — помогите улучшить качество соединения';
                    $body = "Здравствуйте!\n\n"
                        ."Ваш пробный период AVA VPN завершился. Будем благодарны за обратную связь:\n"
                        ."как у вас работали скорость, стабильность и подключение в целом.\n\n"
                        .'Оставить отзыв можно в личном кабинете: '.url('/cabinet/trial')."\n\n"
                        .'Спасибо, что помогаете нам становиться лучше!';

                    Mail::raw($body, function ($m) use ($email, $subject): void {
                        $m->to($email)->subject($subject);
                    });

                    $request->update(['email_sent_at' => now()]);
                    $emails++;
                } catch (\Throwable $e) {
                    Log::warning('trial-feedback mail failed', [
                        'user_id' => $user->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            });

        $this->info("Создано запросов: {$created}; отправлено email: {$emails}");

        return self::SUCCESS;
    }
}
