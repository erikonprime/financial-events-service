<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountingTransaction;
use App\Enum\AccountType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<AccountingTransaction>
 */
class AccountingTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingTransaction::class);
    }

    public function getBalancesByAccount(AccountType $account): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.currency, t.direction, SUM(t.amount) as total')
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->groupBy('t.currency, t.direction')
            ->getQuery()
            ->getResult();
    }
}
