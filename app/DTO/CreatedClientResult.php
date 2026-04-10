<?php

namespace App\DTO;

readonly class CreatedClientResult
{
    public function __construct(
        public string $connectionUrl,
        public ?string $panelClientId,
        public array $raw,
    ) {}
}
