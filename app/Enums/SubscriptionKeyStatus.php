<?php

namespace App\Enums;

enum SubscriptionKeyStatus: string
{
    case Available = 'available';
    case Issued = 'issued';
    case Activated = 'activated';
}
