<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Enum\AccountType;
use App\Enum\DirectionType;
use App\Repository\AccountingTransactionRepository;
use App\Service\BalanceCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BalanceCalculatorTest extends TestCase
{

    private AccountingTransactionRepository&MockObject $repository;
    private BalanceCalculator $balanceCalculator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AccountingTransactionRepository::class);
        $this->balanceCalculator = new BalanceCalculator($this->repository);
    }

    public function testCalculateBalance(): void
    {
        $accountType = AccountType::USER_ACCOUNT;

        $mockResults = [
            ['currency' => 'USD', 'direction' => DirectionType::CREDIT, 'total' => '100.50'],
            ['currency' => 'USD', 'direction' => DirectionType::DEBIT, 'total' => '20.25'],
            ['currency' => 'EUR', 'direction' => DirectionType::CREDIT, 'total' => '50.00'],
            ['currency' => 'GBP', 'direction' => DirectionType::CREDIT, 'total' => '10.00'],
            ['currency' => 'GBP', 'direction' => DirectionType::DEBIT, 'total' => '100.00'],
        ];

        $this->repository
            ->expects($this->once())
            ->method('getBalancesByAccount')
            ->with($accountType)
            ->willReturn($mockResults);

        $result = $this->balanceCalculator->calculateBalance($accountType);

        $expected = [
            [
                'currency' => 'USD',
                'balance' => '80.25',
            ],
            [
                'currency' => 'EUR',
                'balance' => '50.00',
            ],
            [
                'currency' => 'GBP',
                'balance' => '-90.00',
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCalculateBalanceWithNoTransactions(): void
    {
        $accountType = AccountType::FEE_ACCOUNT;

        $this->repository
            ->method('getBalancesByAccount')
            ->willReturn([]);

        $result = $this->balanceCalculator->calculateBalance($accountType);

        $this->assertEmpty($result);
    }
}
