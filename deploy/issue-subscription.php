<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\SharedVpnAccess;

$email = $argv[1] ?? null;
if (! $email) {
    fwrite(STDERR, "Usage: php issue-subscription.php user@email.com\n");
    exit(1);
}

$user = User::query()->where('email', $email)->first();
if (! $user) {
    fwrite(STDERR, "User not found: {$email}\n");
    exit(1);
}

$plan = Plan::query()->where('slug', 'premium-90')->first();
if (! $plan) {
    $plan = Plan::query()->create([
        'name' => 'Премиум',
        'slug' => 'premium-90',
        'devices' => 10,
        'days' => 90,
        'price' => 0,
        'discount' => 0,
        'is_popular' => false,
        'sort_order' => 99,
        'traffic_gb' => 1000,
        'is_active' => false,
    ]);
}

$subId = SharedVpnAccess::ensureVpnSubId($user);

$subscription = Subscription::create([
    'user_id' => $user->id,
    'plan_id' => $plan->id,
    'status' => 'active',
    'purchase_source' => 'manual',
    'max_devices' => 10,
    'starts_at' => now(),
    'expires_at' => now()->addDays(90),
]);

$url = SharedVpnAccess::subscriptionUrl($user);

echo json_encode([
    'user_id' => $user->id,
    'email' => $user->email,
    'subscription_id' => $subscription->id,
    'expires_at' => $subscription->expires_at->toIso8601String(),
    'max_devices' => $subscription->max_devices,
    'traffic_gb' => $plan->traffic_gb,
    'subscription_url' => $url,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
