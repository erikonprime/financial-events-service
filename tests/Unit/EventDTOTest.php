<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Dto\EventDTO;
use PHPUnit\Framework\TestCase;

class EventDTOTest extends TestCase
{
    public function testFromArray(): void
    {
        $payload = [
            'event_id' => 'evt_001',
            'type' => 'payment_received',
            'amount' => 10.50,
            'currency' => 'USD',
            'timestamp' => '2026-03-21T14:46:00Z',
        ];

        $dto = EventDTO::fromArray($payload);

        $this->assertSame('evt_001', $dto->eventId);
        $this->assertSame('payment_received', $dto->type);
        $this->assertSame(10.50, $dto->amount);
        $this->assertSame('USD', $dto->currency);
        $this->assertSame('2026-03-21T14:46:00Z', $dto->timestamp);
    }

    public function testToArray(): void
    {
        $dto = new EventDTO(
            eventId: 'evt_001',
            type: 'payout_created',
            amount: 50.0,
            currency: 'GBP',
            timestamp: '2026-03-21T12:00:00Z'
        );

        $expectedArray = [
            'event_id' => 'evt_001',
            'type' => 'payout_created',
            'amount' => 50.0,
            'currency' => 'GBP',
            'timestamp' => '2026-03-21T12:00:00Z',
        ];

        $this->assertSame($expectedArray, $dto->toArray());
    }
}
