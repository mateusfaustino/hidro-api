<?php

namespace App\Domain\Payments;

interface PaymentsRepository
{
    public function findById(string $id): ?Payment;
    public function save(Payment $payment): void;
    public function delete(Payment $payment): void;
}