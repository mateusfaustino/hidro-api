<?php

namespace App\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', name: 'api_v1_')]
class HelloWorldController extends AbstractController
{
    #[Route('/hello-world', name: 'hello_world', methods: ['GET'])]
    public function helloWorld(): JsonResponse
    {
        return $this->json([
            'message' => 'Hello World!',
            'version' => '1.0',
            'timestamp' => date('c')
        ]);
    }
}