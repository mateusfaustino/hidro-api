<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Exception\InvalidRefreshTokenException;
use App\Domain\Users\User;

interface TokenManagerInterface
{
    public function issueTokenPair(User $user, ?string $ipAddress = null, ?string $userAgent = null): TokenPair;

    /**
     * @throws InvalidRefreshTokenException
     */
    public function rotateRefreshToken(string $refreshToken, ?string $ipAddress = null, ?string $userAgent = null): TokenPair;

    public function revokeRefreshToken(string $refreshToken, ?string $ipAddress = null): void;
}
