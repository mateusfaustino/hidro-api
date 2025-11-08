<?php

namespace App\Presentation\Http\Response;

use App\Application\DTO\FeeDTO;

class FeeResponse
{
    public string $id;
    public string $name;
    public float $amount;
    public string $createdAt;
    
    public function __construct(FeeDTO $feeDTO)
    {
        $this->id = $feeDTO->id;
        $this->name = $feeDTO->name;
        $this->amount = $feeDTO->amount;
        $this->createdAt = date('c');
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'created_at' => $this->createdAt,
        ];
    }
}