<?php

namespace App\Application\DTO;

class LoginRequestDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: trim((string) ($data['email'] ?? '')),
            password: (string) ($data['password'] ?? ''),
        );
    }
}
