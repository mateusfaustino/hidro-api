<?php

namespace App\Application\UseCase;

use App\Application\Service\FeeService;
use App\Application\DTO\FeeDTO;

class CreateFeeUseCase implements UseCaseInterface
{
    private FeeService $feeService;
    
    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }
    
    public function execute(mixed $request = null): mixed
    {
        if (!is_array($request) || !isset($request['id'], $request['name'], $request['amount'])) {
            throw new \InvalidArgumentException('Invalid request data');
        }
        
        $fee = $this->feeService->createFee(
            $request['id'],
            $request['name'],
            $request['amount']
        );
        
        return FeeDTO::fromFee($fee);
    }
}