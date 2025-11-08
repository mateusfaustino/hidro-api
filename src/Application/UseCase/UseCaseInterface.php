<?php

namespace App\Application\UseCase;

interface UseCaseInterface
{
    public function execute(mixed $request = null): mixed;
}