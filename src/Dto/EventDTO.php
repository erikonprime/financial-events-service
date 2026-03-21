<?php

namespace App\Dto;

use App\Enum\EventType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class EventDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'event_id is required')]
        public string $eventId = '',
        #[Assert\Choice(callback: [EventType::class, 'values'], message: 'Invalid event type')]
        public string $type = '',
        #[Assert\NotNull(message: 'amount is required')]
        #[Assert\Positive(message: 'amount must be positive')]
        public float $amount = 0.0,
        #[Assert\NotBlank(message: 'currency is required')]
        public string $currency = '',
        #[Assert\NotBlank(message: 'timestamp is required')]
        public string $timestamp = '',
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            eventId: (string)($payload['event_id'] ?? ''),
            type: (string)($payload['type'] ?? ''),
            amount: (float)($payload['amount'] ?? 0.0),
            currency: (string)($payload['currency'] ?? ''),
            timestamp: (string)($payload['timestamp'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            "event_id" => $this->eventId,
            "type" => $this->type,
            "amount" => $this->amount,
            "currency" => $this->currency,
            "timestamp" => $this->timestamp,
        ];
    }
}
