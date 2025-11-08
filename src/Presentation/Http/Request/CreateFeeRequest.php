<?php

namespace App\Presentation\Http\Request;

class CreateFeeRequest
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
    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['amount'] ?? 0.0
        );
    }
}