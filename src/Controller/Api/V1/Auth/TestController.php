<?php

namespace App\Controller\Api\V1\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/auth', name: 'auth_test_')]
class TestController extends AbstractController
{
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return $this->json(['message' => 'Test route working']);
    }
}