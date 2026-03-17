<?php

declare(strict_types=1);

namespace App\Enum;

enum EventType: string
{
    case PAYMENT_RECEIVED = 'payment_received';
    case PAYMENT_SENT = 'payment_sent';
    case FEE_CHARGED = 'fee_charged';
}
