<?php

/**
 * Домены одноразовой почты — регистрация и смена email запрещены.
 * Дополнительно: BLOCKED_EMAIL_DOMAINS=foo.com,bar.net в .env
 */
$defaults = [
    'deltajohnsons.com',
];

$extra = array_filter(array_map(
    static fn (string $s): string => strtolower(trim($s)),
    explode(',', (string) env('BLOCKED_EMAIL_DOMAINS', '')),
));

return [
    'domains' => array_values(array_unique(array_merge($defaults, $extra))),
];
