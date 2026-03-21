<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccountingTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\DirectionType;


/**
 * @extends ServiceEntityRepository<AccountingTransaction>
 */
class AccountingTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingTransaction::class);
    }

    public function getBalanceByDirectionType(string $account, DirectionType $directionType): float
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->where('t.account = :account')->setParameter('account', $account)
            ->andWhere('t.direction = :direction')->setParameter('direction', $directionType);

        return ($qb->getQuery()->getSingleScalarResult() ?: 0.00);
    }
}
