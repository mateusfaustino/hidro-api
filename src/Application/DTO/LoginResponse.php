<?php

namespace App\Application\DTO;

class LoginResponse
{
    public string $token;
    public string $refreshToken;
    public int $expiresIn;
    
    public function __construct(string $token, string $refreshToken, int $expiresIn)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
    }
    
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn
        ];
    }
}