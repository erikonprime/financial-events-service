<?php

declare(strict_types=1);

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Dto\EventDTO;
use App\Entity\EventProcessed;
use App\Exception\DatabasePersistenceException;
use App\Exception\DuplicateEventException;
use App\Service\EventProcessor;
use App\Repository\EventProcessedRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class EventProcessorTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private EventProcessedRepository&MockObject $eventProcessedRepository;
    private EventProcessor $eventProcessor;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->eventProcessedRepository = $this->createMock(EventProcessedRepository::class);

        $this->eventProcessor = new EventProcessor(
            $this->entityManager,
            $this->eventProcessedRepository,
        );
    }

    public function testProcessEventSuccessfully(): void
    {
        $eventDTO = new EventDTO(
            eventId: 'evt_test_001',
            type: 'payment_received',
            amount: 100.50,
            currency: 'USD',
            timestamp: '2026-03-21T18:59:00+00:00',
        );

        $this->eventProcessedRepository
            ->expects($this->once())
            ->method('existsByEventId')
            ->with('evt_test_001')
            ->willReturn(false);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('persist')->with(
            $this->isInstanceOf(EventProcessed::class),
        );
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('commit');
        $this->entityManager->expects($this->never())->method('rollback');

        $this->eventProcessor->processEvent($eventDTO);
    }

    public function testProcessEventThrowsDuplicateException(): void
    {
        $eventDTO = new EventDTO(eventId: 'evt_test_001');

        $this->eventProcessedRepository
            ->expects($this->once())
            ->method('existsByEventId')
            ->with('evt_test_001')
            ->willReturn(true);

        $this->expectException(DuplicateEventException::class);

        $this->eventProcessor->processEvent($eventDTO);
    }

    public function testProcessEventThrowDatabasePersistenceException(): void
    {
        $eventDTO = new EventDTO(
            eventId: 'evt_test_002',
            type: 'payment_received',
            amount: 50.0,
            currency: 'EUR',
            timestamp: '2026-03-21T18:59:00+00:00',
        );

        $this->eventProcessedRepository
            ->expects($this->once())
            ->method('existsByEventId')
            ->with('evt_test_002')
            ->willReturn(false);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('flush')->willThrowException(
            new \Exception('Database error'),
        );

        $this->entityManager->expects($this->once())->method('rollback');
        $this->entityManager->expects($this->never())->method('commit');

        $this->expectException(DatabasePersistenceException::class);

        $this->eventProcessor->processEvent($eventDTO);
    }

}
