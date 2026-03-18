<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\EventDTO;
use App\Service\EventProcessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends AbstractController
{
    public function __construct(
        private readonly EventProcessor $eventProcessor,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/events', name: 'event_store', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $eventDto = EventDTO::fromArray($payload);

        $this->validate($eventDto);

        $this->eventProcessor->processEvent($eventDto);

        return $this->json(null);
    }

    private function validate(EventDTO $eventDTO): void
    {
        $violations = $this->validator->validate($eventDTO);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $v) {
                $errors[] = $v->getMessage();
            }

            throw new \RuntimeException(implode(', ', $errors));
        }
    }
}
