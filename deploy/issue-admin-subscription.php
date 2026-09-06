<?php

/**
 * Бессрочная подписка без лимита трафика — для админа и проверки узлов.
 *
 * Запуск:  php deploy/issue-admin-subscription.php [email]
 * По умолчанию email — admin@avavpn.ru.
 *
 * Идемпотентно: повторный запуск не плодит подписки, а продлевает существующую.
 * Тариф скрыт от витрины (is_active = false), трафик 0 = безлимит.
 * Отзыв доступа: снять статус active у подписки в /admin или
 *   php artisan tinker --execute="App\Models\Subscription::find(<id>)->update(['status'=>'cancelled']);"
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\SharedVpnAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

$email = $argv[1] ?? 'admin@avavpn.ru';

$user = User::query()->where('email', $email)->first();
$created = false;

if (! $user) {
    // Пароль случайный и намеренно никуда не выводится: учётка нужна только как
    // владелец подписки. Понадобится вход — сбросьте пароль через «забыли пароль».
    $user = User::create([
        'name' => 'Admin',
        'email' => $email,
        'password' => Str::random(48),
        'trial_used' => true,
    ]);
    $user->forceFill(['email_verified_at' => now()])->save();
    $created = true;
}

$plan = Plan::query()->firstOrCreate(
    ['slug' => 'admin-unlimited'],
    [
        'name' => 'Админ · безлимит',
        'devices' => 100,
        'days' => 36500,
        'price' => 0,
        'discount' => 0,
        'is_popular' => false,
        'is_active' => false, // не показывать в тарифах на сайте
        'sort_order' => 999,
        'traffic_gb' => 0,    // 0 = без лимита
    ]
);

// 2099 год: в subscription-userinfo уходит expire=<timestamp>, клиент показывает
// его как срок действия. Заголовок total=0 уже означает безлимит по трафику.
$expiresAt = Carbon::create(2099, 12, 31, 23, 59, 59);

$subscription = Subscription::query()
    ->where('user_id', $user->id)
    ->where('plan_id', $plan->id)
    ->first();

if ($subscription) {
    $subscription->update([
        'status' => 'active',
        'expires_at' => $expiresAt,
        'max_devices' => $plan->devices,
    ]);
} else {
    $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'purchase_source' => 'admin',
        'max_devices' => $plan->devices,
        'starts_at' => now(),
        'expires_at' => $expiresAt,
    ]);
}

echo json_encode([
    'user_created' => $created,
    'user_id' => $user->id,
    'email' => $user->email,
    'subscription_id' => $subscription->id,
    'expires_at' => $subscription->expires_at->toIso8601String(),
    'max_devices' => $subscription->max_devices,
    'traffic_gb' => $plan->traffic_gb,
    'sub_id' => SharedVpnAccess::ensureVpnSubId($user),
    'subscription_url' => SharedVpnAccess::subscriptionUrl($user),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
