<?php

namespace App\Support;

use App\Models\TrialKey;
use App\Models\User;
use Illuminate\Support\Str;

class SharedVpnAccess
{
    /**
     * Название подписки в клиенте (Happ: HTTP-заголовок profile-title, макс. 25 символов).
     */
    public const PROFILE_TITLE = 'AVA VPN';

    public const EXPIRED_PROFILE_TITLE = 'AVA VPN · истекла';

    private const EXPIRED_NODE_STUB = 'vless://00000000-0000-0000-0000-000000000000@127.0.0.1:1?encryption=none';

    /**
     * Подписи узлов в Happ. Ключ — config-ключ URI.
     * name  — отображаемое имя (фрагмент #..., поддерживает эмодзи-флаги).
     * desc  — подпись под именем вместо «VLESS»/«Hysteria2» (Happ: serverDescription).
     *
     * @var array<string, array{name: string, desc: ?string}>
     */
    private const NODE_LABELS = [
        'shared_home_uri' => ['name' => '🇫🇮 Домашний интернет', 'desc' => null],
        'shared_cellular_uri' => ['name' => '🇷🇺 Сотовая сеть', 'desc' => null],
    ];

    /**
     * Сырые URI из конфигурации (как заданы в .env), для отображения в админке.
     *
     * @return list<string>
     */
    public static function nodeUris(): array
    {
        $uris = [];

        foreach (array_keys(self::NODE_LABELS) as $key) {
            $uri = trim((string) config("vpn.{$key}", ''));
            if ($uri !== '') {
                $uris[] = $uri;
            }
        }

        return $uris;
    }

    /**
     * URI с подписями для Happ: имя узла и serverDescription.
     * Любой существующий #fragment в URI заменяется на наш.
     *
     * @return list<string>
     */
    public static function namedNodeUris(): array
    {
        $uris = [];

        foreach (self::NODE_LABELS as $key => $label) {
            $uri = trim((string) config("vpn.{$key}", ''));
            if ($uri === '') {
                continue;
            }

            $uris[] = self::labelNodeUri($uri, $label);
        }

        return $uris;
    }

    /**
     * @param  array{name: string, desc: ?string}  $label
     */
    private static function labelNodeUri(string $uri, array $label): string
    {
        // У vmess имя узла лежит в поле ps внутри base64-конфига, а не во
        // #fragment: дописанный к vmess://-ссылке фрагмент клиент игнорирует,
        // и узел показался бы пользователю своим исходным именем из ссылки.
        if (str_starts_with(strtolower($uri), 'vmess://')) {
            $labelled = self::labelVmessUri($uri, $label['name']);
            if ($labelled !== null) {
                return $labelled;
            }
        }

        $hashPos = strpos($uri, '#');
        if ($hashPos !== false) {
            $uri = substr($uri, 0, $hashPos);
        }

        $fragment = $label['name'];
        if (! empty($label['desc'])) {
            $fragment .= '?serverDescription=' . base64_encode($label['desc']);
        }

        return $uri . '#' . $fragment;
    }

    /**
     * Переписывает ps (отображаемое имя) внутри vmess://-конфига.
     * serverDescription для vmess не поддерживается — Happ читает его только
     * из #fragment, которого у vmess нет.
     *
     * @return string|null null, если ссылка не разбирается — тогда вызывающий
     *                     код падает на обычную схему с #fragment.
     */
    private static function labelVmessUri(string $uri, string $name): ?string
    {
        $payload = substr($uri, strlen('vmess://'));

        $hashPos = strpos($payload, '#');
        if ($hashPos !== false) {
            $payload = substr($payload, 0, $hashPos);
        }

        // Ссылки встречаются и в base64url, и без padding.
        $payload = strtr(trim($payload), '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);

        $json = base64_decode($payload, true);
        if ($json === false || $json === '') {
            return null;
        }

        $config = json_decode($json, true);
        if (! is_array($config)) {
            return null;
        }

        $config['ps'] = $name;

        $encoded = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            return null;
        }

        return 'vmess://'.base64_encode($encoded);
    }

    public static function subscriptionBody(): string
    {
        $uris = self::namedNodeUris();
        if ($uris === []) {
            return '';
        }

        return base64_encode(implode("\n", $uris));
    }

    public static function subscriptionPublicId(User $user): string
    {
        return self::ensureVpnSubId($user);
    }

    public static function subscriptionUrl(User $user): string
    {
        return route('subscription.show', [
            'subId' => self::subscriptionPublicId($user),
        ], absolute: true);
    }

    public static function ensureVpnSubId(User $user): string
    {
        if ($user->vpn_sub_id) {
            return $user->vpn_sub_id;
        }

        do {
            $id = Str::random(16);
        } while (
            User::query()->where('vpn_sub_id', $id)->exists()
            || TrialKey::query()->where('sub_id', $id)->exists()
        );

        $user->forceFill(['vpn_sub_id' => $id])->save();

        return $id;
    }

    public static function userHasAccess(User $user): bool
    {
        if ($user->activeSubscriptions()->exists()) {
            return true;
        }

        $trial = $user->trialKey;

        return $trial !== null && $trial->isActive();
    }

    public static function connectionUriForUser(User $user): ?string
    {
        if (self::nodeUris() === []) {
            return null;
        }

        if (! self::userHasAccess($user)) {
            return $user->vpn_sub_id ? self::subscriptionUrl($user) : null;
        }

        return self::subscriptionUrl($user);
    }

    /**
     * Тело подписки для истёкшего доступа: routing off + заглушки с подписью «Подписка истекла».
     * Happ получает HTTP 200, сбрасывает старые серверы и показывает announce.
     */
    public static function expiredSubscriptionBody(): string
    {
        $lines = [HappRouting::ROUTING_OFF];

        $labels = [
            '⛔ Подписка истекла',
            'Продлите на avavpn.ru',
        ];

        $slot = 0;
        foreach (array_keys(self::NODE_LABELS) as $key) {
            if (trim((string) config("vpn.{$key}", '')) === '') {
                continue;
            }

            $label = $labels[$slot] ?? $labels[0];
            $lines[] = self::EXPIRED_NODE_STUB.'#'.$label;
            $slot++;
        }

        if ($slot === 0) {
            $lines[] = self::EXPIRED_NODE_STUB.'#'.$labels[0];
        }

        return base64_encode(implode("\n", $lines));
    }

    /**
     * @deprecated Используйте nodeUris() или subscriptionUrl()
     */
    public static function connectionUri(): string
    {
        $uris = self::nodeUris();

        return $uris[0] ?? '';
    }

    public static function trialIsActive(?TrialKey $trialKey): bool
    {
        return $trialKey !== null && $trialKey->isActive();
    }

    public static function activeTrialKey(User $user): ?TrialKey
    {
        $trial = $user->trialKey;

        return self::trialIsActive($trial) ? $trial : null;
    }

    public static function accessExpiresAt(User $user): ?\Illuminate\Support\Carbon
    {
        if (! self::userHasAccess($user)) {
            return self::lastAccessExpiresAt($user);
        }

        $latest = null;

        foreach ($user->activeSubscriptions()->get() as $subscription) {
            if ($subscription->expires_at && ($latest === null || $subscription->expires_at->gt($latest))) {
                $latest = $subscription->expires_at;
            }
        }

        $trial = self::activeTrialKey($user);
        if ($trial?->expires_at) {
            if ($latest === null || $trial->expires_at->gt($latest)) {
                $latest = $trial->expires_at;
            }
        }

        return $latest;
    }

    public static function lastAccessExpiresAt(User $user): ?\Illuminate\Support\Carbon
    {
        $latest = null;

        foreach ($user->subscriptions()->orderByDesc('expires_at')->get() as $subscription) {
            if ($subscription->expires_at && ($latest === null || $subscription->expires_at->gt($latest))) {
                $latest = $subscription->expires_at;
            }
        }

        $trial = $user->trialKey;
        if ($trial?->expires_at) {
            if ($latest === null || $trial->expires_at->gt($latest)) {
                $latest = $trial->expires_at;
            }
        }

        return $latest;
    }

    public static function resolveUserBySubId(string $subId): ?User
    {
        $user = User::query()->where('vpn_sub_id', $subId)->first();
        if ($user) {
            return $user;
        }

        $trialKey = TrialKey::query()->where('sub_id', $subId)->with('user')->first();

        return $trialKey?->user;
    }
}
