<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\EventDTO;
use App\Exception\IApiException;
use App\Exception\IApiValidationException;
use App\Service\EventProcessor;
use App\Validator\EventValidator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class EventsController extends AbstractController
{
    public function __construct(
        private readonly EventProcessor $eventProcessor,
        private readonly EventValidator $validator,
    ) {}

    #[Route('/events', name: 'event_store', methods: ['POST'])]
    #[OA\Post(
        summary: 'Store a new financial event',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: EventDTO::class))
        ),
        responses: [
            new OA\Response(response: 200, description: 'Event processed successfully'),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 500, description: 'Internal server error'),
            new OA\Response(response: 409, description: 'Event already processed'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    #[OA\Tag(name: 'Events')]
    public function __invoke(Request $request): Response
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $eventDto = EventDTO::fromArray($payload);
            $this->validator->validate($eventDto);
            $this->eventProcessor->processEvent($eventDto);
        } catch (\JsonException $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (IApiException $e) {
            return $this->json($e->getMessage(), $e->getCode());
        } catch (IApiValidationException $e) {
            return $this->json($e->toArray(), $e->getCode());
        } catch (\Throwable $e) {
            return $this->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(null);
    }

}
