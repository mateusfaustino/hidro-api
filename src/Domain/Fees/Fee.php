<?php

namespace App\Domain\Fees;

use App\Domain\Common\AggregateRoot;

class Fee implements AggregateRoot
{
    private string $id;
    private string $name;
    private float $amount;
    
    public function __construct(string $id, string $name, float $amount)
    {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getAmount(): float
    {
        return $this->amount;
    }
}