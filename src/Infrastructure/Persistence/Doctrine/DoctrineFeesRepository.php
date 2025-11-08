<?php

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Fees\Fee;
use App\Domain\Fees\FeesRepository;

class DoctrineFeesRepository implements FeesRepository
{
    public function findById(string $id): ?Fee
    {
        // Implementation will be added later
        return null;
    }
    
    public function save(Fee $fee): void
    {
        // Implementation will be added later
    }
    
    public function delete(Fee $fee): void
    {
        // Implementation will be added later
    }
}