<?php

namespace App\Dto;

readonly class EventDTO
{

    /**
     *
     * {
     * "event_id": "evt_123",
     * "type": "payment_received",
     * "amount": 100.00,
     * "currency": "EUR",
     * "timestamp": "2026-01-01T00:00:00Z"
     * }
     */

    public function __construct(
        public string $eventId,
        public string $type,
        public float $amount,
        public string $currency,
        public string $timestamp,
    ) {}
}
