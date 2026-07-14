<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\User::orderByDesc('id')->limit(15)->get(['id', 'email', 'name', 'vpn_sub_id']) as $u) {
    echo $u->id.' | '.$u->email.' | '.($u->vpn_sub_id ?? '-').PHP_EOL;
}
