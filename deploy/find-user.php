<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = App\Models\User::query()
    ->where('email', 'like', '%oblik%')
    ->orWhere('email', 'like', '%ava%')
    ->orWhere('id', '<=', 5)
    ->orderBy('id')
    ->get(['id', 'email', 'name', 'vpn_sub_id']);

foreach ($rows as $u) {
    echo $u->id.' | '.$u->email.' | '.($u->vpn_sub_id ?? '-').PHP_EOL;
}

$withSub = App\Models\Subscription::query()->where('status', 'active')->where('expires_at', '>', now())->with('user:id,email')->latest()->limit(5)->get();
echo "--- active subs ---\n";
foreach ($withSub as $s) {
    echo 'sub#'.$s->id.' user='.$s->user?->email.' devices='.$s->max_devices.' until='.$s->expires_at->format('Y-m-d')."\n";
}
