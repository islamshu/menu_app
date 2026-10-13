<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Trialing => 'Trialing',
            self::PastDue => 'Past due',
            self::Canceled => 'Canceled',
            self::Expired => 'Expired',
        };
    }

    public function isUsable(): bool
    {
        return in_array($this, [self::Active, self::Trialing], true);
    }
}
