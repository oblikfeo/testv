<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class NotDisposableEmailDomain implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! str_contains($value, '@')) {
            return;
        }

        $domain = strtolower(trim(Str::after($value, '@')));
        if ($domain === '') {
            return;
        }

        foreach (config('disposable_email.domains', []) as $blocked) {
            $blocked = strtolower(trim((string) $blocked));
            if ($blocked === '') {
                continue;
            }
            if ($domain === $blocked || str_ends_with($domain, '.'.$blocked)) {
                $fail(__('validation.disposable_email'));

                return;
            }
        }
    }
}
