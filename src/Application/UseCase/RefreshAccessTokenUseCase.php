<?php

namespace App\Application\UseCase;

use App\Application\DTO\RefreshTokenRequestDTO;
use App\Application\DTO\TokenPairDTO;
use App\Domain\Auth\TokenManagerInterface;

class RefreshAccessTokenUseCase
{
    public function __construct(private readonly TokenManagerInterface $tokenManager)
    {
    }

    public function execute(RefreshTokenRequestDTO $request, ?string $ipAddress = null, ?string $userAgent = null): TokenPairDTO
    {
        $tokenPair = $this->tokenManager->rotateRefreshToken($request->refreshToken, $ipAddress, $userAgent);

        return TokenPairDTO::fromTokenPair($tokenPair);
    }
}
