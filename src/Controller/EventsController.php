<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\EventDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends AbstractController
{
    #[Route('/events', name: 'event_store', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $eventDto = new EventDto(
            eventId: (string) ($payload['event_id'] ?? ''),
            type: (string) ($payload['type'] ?? ''),
            amount: (float) ($payload['amount'] ?? 0),
            currency: (string) ($payload['currency'] ?? ''),
            timestamp: (string) ($payload['timestamp'] ?? ''),
        );

        return $this->json(null);
    }
}
