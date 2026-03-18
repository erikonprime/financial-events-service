<?php

namespace App\Service;

use App\Dto\EventDTO;
use App\Entity\AccountingTransaction;
use App\Entity\EventProcessed;
use App\Enum\AccountType;
use App\Enum\DirectionType;
use App\Enum\EventType;
use App\Repository\EventProcessedRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

readonly class EventProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventProcessedRepository $eventProcessedRepository,
    ) {}

    public function processEvent(EventDTO $eventDTO): void
    {
        if ($this->eventProcessedRepository->existsByEventId($eventDTO->eventId)) {
            throw new \RuntimeException('dublicated');
        }

        $type = EventType::from($eventDTO->type);

        $amount = number_format($eventDTO->amount, 2, '.', '');
        $currency = strtoupper($eventDTO->currency);
        $eventTimestamp = new DateTimeImmutable($eventDTO->timestamp);

        $transactions = $this->buildTransaction($type, $amount, $currency, $eventTimestamp);

        $eventProcessed = new EventProcessed($eventDTO->eventId, $eventDTO->toArray());

        foreach ($transactions as $transaction) {
            $eventProcessed->addTransaction($transaction);
        }
        $this->entityManager->persist($eventProcessed);
        $this->entityManager->flush();
    }


    private function buildTransaction(
        EventType $type,
        string $amount,
        string $currency,
        $eventTimestamp,
    ): array {
        return match ($type) {
            EventType::PAYMENT_RECEIVED => [
                new AccountingTransaction(
                    AccountType::USER_ACCOUNT,
                    DirectionType::DEBIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
                new AccountingTransaction(
                    AccountType::SYSTEM_CASH_ACCOUNT,
                    DirectionType::CREDIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
            ],
            EventType::PAYMENT_SENT => [
                new AccountingTransaction(
                    AccountType::SYSTEM_CASH_ACCOUNT,
                    DirectionType::DEBIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
                new AccountingTransaction(
                    AccountType::USER_ACCOUNT,
                    DirectionType::CREDIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
            ],
            EventType::FEE_CHARGED => [
                new AccountingTransaction(
                    AccountType::USER_ACCOUNT,
                    DirectionType::DEBIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
                new AccountingTransaction(
                    AccountType::FEE_ACCOUNT,
                    DirectionType::CREDIT,
                    $amount,
                    $currency,
                    $eventTimestamp,
                ),
            ],
        };
    }
}
