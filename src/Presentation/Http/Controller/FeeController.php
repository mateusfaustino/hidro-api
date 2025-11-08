<?php

namespace App\Presentation\Http\Controller;

use App\Application\Service\FeeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/fees', name: 'fees_')]
class FeeController
{
    private FeeService $feeService;
    
    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }
    
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Implementation will be added later
        return new JsonResponse([]);
    }
    
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Implementation will be added later
        return new JsonResponse([]);
    }
    
    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        // Implementation will be added later
        return new JsonResponse([]);
    }
}