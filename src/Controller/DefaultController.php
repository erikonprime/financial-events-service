<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Simple Financial events Service',
        ]);
    }
}
