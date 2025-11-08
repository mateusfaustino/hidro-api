<?php

namespace App\Application\DTO;

use App\Domain\Fees\Fee;

class FeeDTO
{
    public string $id;
    public string $name;
    public float $amount;
    
    public function __construct(string $id, string $name, float $amount)
    {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
    }
    
    public static function fromFee(Fee $fee): self
    {
        return new self(
            $fee->getId(),
            $fee->getName(),
            $fee->getAmount()
        );
    }
}