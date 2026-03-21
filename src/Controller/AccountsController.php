<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\DirectionType;
use App\Repository\AccountingTransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AccountsController extends AbstractController
{

    public function __construct(
        private readonly AccountingTransactionRepository $transactionRepository,
    ) {}

    #[Route('/accounts/{account}/balance', name: 'account_balance', methods: ['GET'])]
    public function getBalance(string $account): JsonResponse
    {
        $debitBalance = $this->transactionRepository->getBalanceByDirectionType($account, DirectionType::DEBIT);
        $creditBalance = $this->transactionRepository->getBalanceByDirectionType($account, DirectionType::CREDIT);

        return $this->json([
            'account' => $account,
            'balance' => $creditBalance - $debitBalance,
        ]);
    }

    #[Route('/accounts/{account}/transactions', name: 'account_transactions', methods: ['GET'])]
    public function getTransactions(string $account): JsonResponse
    {
        $transactions = $this->transactionRepository->findBy(['account' => $account]);

        return $this->json([
            'account' => $account,
            'transactions' => $transactions,
        ], context: ['groups' => 'transaction:list']);
    }
}
