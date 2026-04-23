<?php

namespace App\Services;

class TelegramLoginService
{
    /**
     * Verify Telegram Login Widget signature.
     *
     * @param  array<string, mixed>  $data
     */
    public function verify(array $data, string $botToken): bool
    {
        $hash = (string) ($data['hash'] ?? '');
        if ($hash === '') {
            return false;
        }

        // Build data_check_string: key=value sorted by key, excluding "hash".
        $pairs = [];
        foreach ($data as $k => $v) {
            if ($k === 'hash') {
                continue;
            }
            if (is_array($v) || is_object($v)) {
                continue;
            }
            $pairs[(string) $k] = (string) $v;
        }

        ksort($pairs);

        $checkStringParts = [];
        foreach ($pairs as $k => $v) {
            $checkStringParts[] = $k.'='.$v;
        }
        $checkString = implode("\n", $checkStringParts);

        $secretKey = hash('sha256', $botToken, true);
        $calc = hash_hmac('sha256', $checkString, $secretKey);

        return hash_equals($calc, $hash);
    }
}

