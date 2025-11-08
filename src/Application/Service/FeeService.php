<?php

namespace App\Application\Service;

use App\Domain\Fees\Fee;
use App\Domain\Fees\FeesRepository;

class FeeService
{
    private FeesRepository $repository;
    
    public function __construct(FeesRepository $repository)
    {
        $this->repository = $repository;
    }
    
    public function createFee(string $id, string $name, float $amount): Fee
    {
        $fee = new Fee($id, $name, $amount);
        $this->repository->save($fee);
        return $fee;
    }
    
    public function getFee(string $id): ?Fee
    {
        return $this->repository->findById($id);
    }
}