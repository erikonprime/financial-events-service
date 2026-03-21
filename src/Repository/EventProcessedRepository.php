<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EventProcessed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventProcessed>
 */
class EventProcessedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventProcessed::class);
    }

    public function existsByEventId(string $eventId): bool
    {
        return null !== $this->findOneBy(['eventId' => $eventId]);
    }
}
