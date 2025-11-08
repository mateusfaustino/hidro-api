<?php

namespace App\Application\DTO;

class LoginRequest
{
    public string $email;
    public string $password;
    
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'] ?? '',
            $data['password'] ?? ''
        );
    }
}