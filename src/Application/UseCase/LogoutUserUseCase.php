<?php

namespace App\Application\UseCase;

use App\Application\DTO\RefreshTokenRequestDTO;
use App\Domain\Auth\TokenManagerInterface;

class LogoutUserUseCase
{
    public function __construct(private readonly TokenManagerInterface $tokenManager)
    {
    }

    public function execute(RefreshTokenRequestDTO $request, ?string $ipAddress = null): bool
    {
        $this->tokenManager->revokeRefreshToken($request->refreshToken, $ipAddress);

        return true;
    }
}
