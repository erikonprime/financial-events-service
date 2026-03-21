<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\EventDTO;
use App\Entity\EventProcessed;
use App\Exception\DatabasePersistenceException;
use App\Exception\DuplicateEventException;
use App\Factory\TransactionFactory;
use App\Repository\EventProcessedRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class EventProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventProcessedRepository $eventProcessedRepository,
    ) {}

    public function processEvent(EventDTO $eventDTO): void
    {
        if ($this->eventProcessedRepository->existsByEventId($eventDTO->eventId)) {
            throw new DuplicateEventException();
        }

        $eventProcessed = new EventProcessed($eventDTO->eventId, $eventDTO->toArray());
        $transactions = TransactionFactory::build($eventDTO);

        foreach ($transactions as $transaction) {
            $eventProcessed->addTransaction($transaction);
        }

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($eventProcessed);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable) {
            $this->entityManager->rollback();
            throw new DatabasePersistenceException();
        }
    }

}
