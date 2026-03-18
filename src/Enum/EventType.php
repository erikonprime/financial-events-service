<?php

declare(strict_types=1);

namespace App\Enum;

enum EventType: string
{
    case PAYMENT_RECEIVED = 'payment_received';
    case PAYMENT_SENT = 'payment_sent';
    case FEE_CHARGED = 'fee_charged';

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
