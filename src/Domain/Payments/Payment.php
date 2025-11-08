<?php

namespace App\Domain\Payments;

class Payment
{
    private string $id;
    private string $feeId;
    private float $amount;
    private string $paymentDate;
    
    public function __construct(string $id, string $feeId, float $amount, string $paymentDate)
    {
        $this->id = $id;
        $this->feeId = $feeId;
        $this->amount = $amount;
        $this->paymentDate = $paymentDate;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getFeeId(): string
    {
        return $this->feeId;
    }
    
    public function getAmount(): float
    {
        return $this->amount;
    }
    
    public function getPaymentDate(): string
    {
        return $this->paymentDate;
    }
}