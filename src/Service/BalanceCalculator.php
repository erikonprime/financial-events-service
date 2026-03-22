<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\AccountType;
use App\Repository\AccountingTransactionRepository;
use App\Enum\DirectionType;

readonly class BalanceCalculator
{
    public function __construct(
        private AccountingTransactionRepository $accountingTransactionRepository,
    ) {}

    public function calculateBalance(AccountType $accountType): array
    {
        $results = $this->accountingTransactionRepository->getBalancesByAccount($accountType);

        $balances = [];
        foreach ($results as $row) {
            $currency = $row['currency'];
            $direction = $row['direction'];
            $amount = (string) $row['total'];

            if (!isset($balances[$currency])) {
                $balances[$currency] = '0.00';
            }

            if ($direction === DirectionType::CREDIT) {
                $balances[$currency] = bcadd($balances[$currency], $amount, 2);
            } else {
                $balances[$currency] = bcsub($balances[$currency], $amount, 2);
            }
        }

        $formattedBalances = [];
        foreach ($balances as $currency => $total) {
            $formattedBalances[] = [
                'currency' => $currency,
                'balance' => $total,
            ];
        }

        return $formattedBalances;
    }
}
