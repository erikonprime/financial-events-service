<?php

declare(strict_types=1);

namespace App\Enum;

enum AccountType: string
{
    case USER_ACCOUNT = 'user_account';
    case SYSTEM_CASH_ACCOUNT = 'system_cash_account';
    case FEE_ACCOUNT = 'fee_account';
}
