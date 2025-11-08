<?php

namespace App\Infrastructure\Security;

class RefreshTokenHasher
{
    public function __construct(private readonly string $pepper)
    {
    }

    public function hash(string $refreshToken): string
    {
        return hash('sha512', $refreshToken . $this->pepper);
    }
}
