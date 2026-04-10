<?php

namespace App\Contracts;

use App\DTO\CreatedClientResult;
use App\Models\Pair;
use Carbon\CarbonInterface;

interface PanelClient
{
    public function createSubscription(Pair $pair): CreatedClientResult;

    public function updateExpiry(Pair $pair, string $panelClientId, CarbonInterface $expiresAt): void;

    /**
     * @return array<string, mixed>|null
     */
    public function getClientTraffic(Pair $pair, string $panelClientId): ?array;
}
