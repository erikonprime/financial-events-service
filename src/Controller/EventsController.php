<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\EventDTO;
use App\Exception\ApiExceptionI;
use App\Exception\ApiValidationExceptionI;
use App\Service\EventProcessor;
use App\Validator\EventValidator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends AbstractController
{
    public function __construct(
        private readonly EventProcessor $eventProcessor,
        private readonly EventValidator $validator,
    ) {}

    #[Route('/events', name: 'event_store', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $eventDto = EventDTO::fromArray($payload);
            $this->validator->validate($eventDto);
            $this->eventProcessor->processEvent($eventDto);
        } catch (\JsonException $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (ApiExceptionI $e) {
            return $this->json($e->getMessage(), $e->getCode());
        } catch (ApiValidationExceptionI $e) {
            return $this->json($e->toArray(), $e->getCode());
        } catch (\Throwable $e) {
            return $this->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(null);
    }

}
