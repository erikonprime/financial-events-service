<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Dto\EventDTO;
use App\Entity\AccountingTransaction;
use App\Enum\DirectionType;
use App\Factory\TransactionFactory;
use PHPUnit\Framework\TestCase;
use App\Enum\AccountType;

final class TransactionFactoryTest extends TestCase
{
    public function testPaymentReceived(): void
    {
        $eventDto = EventDTO::fromArray([
            'eventId' => '123',
            'type' => 'payment_received',
            'amount' => 10.10,
            'currency' => 'CAD',
            'timestamp' => '2026-01-01T00:00:00Z',
        ]);

        $factory = new TransactionFactory();
        $transactions = $factory::build($eventDto);

        /** @var AccountingTransaction $debitAccountTransaction */
        /** @var AccountingTransaction $creditAccountTransaction */
        [$debitAccountTransaction, $creditAccountTransaction] = $transactions;

        self::assertCount(2, $transactions);

        //credit
        self::assertSame(DirectionType::DEBIT, $debitAccountTransaction->getDirection());
        self::assertSame(AccountType::USER_ACCOUNT, $debitAccountTransaction->getAccount());
        self::assertSame('CAD', $debitAccountTransaction->getCurrency());
        self::assertSame(10.10, (float)$debitAccountTransaction->getAmount());

        //debit
        self::assertSame(DirectionType::CREDIT, $creditAccountTransaction->getDirection());
        self::assertSame(AccountType::SYSTEM_CASH_ACCOUNT, $creditAccountTransaction->getAccount());
        self::assertSame('CAD', $creditAccountTransaction->getCurrency());
        self::assertSame(10.10, (float)$creditAccountTransaction->getAmount());
    }

}
