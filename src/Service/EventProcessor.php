<?php

namespace App\Service;

use App\Dto\EventDTO;
use App\Entity\AccountingTransaction;
use App\Enum\Direction;
use App\Enum\EventType;
use Doctrine\ORM\EntityManagerInterface;

readonly class EventProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function processEvent(EventDTO $eventDTO): void
    {
        //check if exist
        $type = EventType::from($eventDTO->type);

        $amount = number_format($eventDTO->amount, 2, '.', '');
        $currency = strtoupper($eventDTO->currency);

        $transactions = $this->buildTransaction(
            $eventDTO->eventId,
            $type,
            $amount,
            $currency
        );

        foreach ($transactions as $transaction) {
            $this->entityManager->persist($transaction);
        }

        $this->entityManager->flush();
    }


    private function buildTransaction(string $eventId, EventType $type, string $amount, string $currency)
    {
        return match ($type) {
            EventType::PAYMENT_RECEIVED => [
                new AccountingTransaction($eventId, 'user_account', $amount, $currency, Direction::DEBIT),
                new AccountingTransaction($eventId, 'system_cash_account', $amount, $currency, Direction::CREDIT),
            ],
            EventType::PAYMENT_SENT => [
                new AccountingTransaction($eventId, 'system_cash_account', $amount, $currency, Direction::DEBIT),
                new AccountingTransaction($eventId, 'user_account', $amount, $currency, Direction::CREDIT),
            ],
            EventType::FEE_CHARGED => [
                new AccountingTransaction($eventId, 'user_account', $amount, $currency, Direction::DEBIT),
                new AccountingTransaction($eventId, 'fee_account', $amount, $currency, Direction::CREDIT),
            ],
        };
    }
}
