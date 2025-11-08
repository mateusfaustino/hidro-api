<?php

namespace App\Domain\Common;

interface AggregateRoot
{
    public function getId(): string;
}