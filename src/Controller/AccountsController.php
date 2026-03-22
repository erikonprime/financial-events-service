<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\DirectionType;
use App\Repository\AccountingTransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use App\Entity\AccountingTransaction;

class AccountsController extends AbstractController
{

    public function __construct(
        private readonly AccountingTransactionRepository $transactionRepository,
    ) {}

    #[Route('/accounts/{account}/balance', name: 'account_balance', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns the calculated balance (Credit - Debit) for a specific account.',
        summary: 'Get account balance',
        parameters: [
            new OA\Parameter(
                name: 'account',
                description: 'The account identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the account balance',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'account', type: 'string', example: '12345'),
                        new OA\Property(property: 'balance', type: 'number', format: 'float', example: 150.50)
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Accounts')]
    public function getBalance(string $account): JsonResponse
    {
        $debitBalance = $this->transactionRepository->getBalanceByDirectionType($account, DirectionType::DEBIT);
        $creditBalance = $this->transactionRepository->getBalanceByDirectionType($account, DirectionType::CREDIT);

        return $this->json([
            'account' => $account,
            'balance' => bcsub($creditBalance, $debitBalance, 2),
        ]);
    }

    #[Route('/accounts/{account}/transactions', name: 'account_transactions', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get transactions for an account',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the list of transactions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: AccountingTransaction::class, groups: ['transaction:list']))
                )
            )
        ]
    )]
    #[OA\Tag(name: 'Accounts')]
    public function getTransactions(string $account): JsonResponse
    {
        $transactions = $this->transactionRepository->findBy(['account' => $account]);

        return $this->json([
            'account' => $account,
            'transactions' => $transactions,
        ], context: ['groups' => 'transaction:list']);
    }
}
