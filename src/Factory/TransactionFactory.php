<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\EventDTO;
use App\Entity\AccountingTransaction;
use App\Enum\AccountType;
use App\Enum\DirectionType;
use App\Enum\EventType;
use DateTimeImmutable;

class TransactionFactory
{
    public static function build(EventDTO $eventDTO): array
    {
        $type = EventType::from($eventDTO->type);
        $amount = number_format($eventDTO->amount, 2, '.', '');
        $currency = strtoupper($eventDTO->currency);
        $eventTimestamp = new DateTimeImmutable($eventDTO->timestamp);

        return match ($type) {
            EventType::PAYMENT_RECEIVED => [
                new AccountingTransaction(
                    AccountType::USER_ACCOUNT,
                    DirectionType::DEBIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
                new AccountingTransaction(
                    AccountType::SYSTEM_CASH_ACCOUNT,
                    DirectionType::CREDIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
            ],
            EventType::PAYMENT_SENT => [
                new AccountingTransaction(
                    AccountType::SYSTEM_CASH_ACCOUNT,
                    DirectionType::DEBIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
                new AccountingTransaction(
                    AccountType::USER_ACCOUNT,
                    DirectionType::CREDIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
            ],
            EventType::FEE_CHARGED => [
                new AccountingTransaction(
                    AccountType::USER_ACCOUNT,
                    DirectionType::DEBIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
                new AccountingTransaction(
                    AccountType::FEE_ACCOUNT,
                    DirectionType::CREDIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
            ],
        };
    }
}
