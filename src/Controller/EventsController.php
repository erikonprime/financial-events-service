<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventsController extends AbstractController
{
    #[Route('/events', name: 'event_store', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome!',
        ]);
    }
}
