<?php

namespace App\Application\DTO;

use App\Domain\Auth\TokenPair;
use DateTimeImmutable;

class TokenPairDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly int $expiresIn,
        public readonly int $refreshExpiresIn,
        public readonly string $tokenType,
        public readonly array $scope,
        public readonly DateTimeImmutable $issuedAt,
        public readonly DateTimeImmutable $accessTokenExpiresAt,
        public readonly DateTimeImmutable $refreshTokenExpiresAt,
    ) {
    }

    public static function fromTokenPair(TokenPair $tokenPair): self
    {
        return new self(
            accessToken: $tokenPair->getAccessToken(),
            refreshToken: $tokenPair->getRefreshToken(),
            expiresIn: $tokenPair->getExpiresIn(),
            refreshExpiresIn: $tokenPair->getRefreshExpiresIn(),
            tokenType: $tokenPair->getTokenType(),
            scope: $tokenPair->getScopes(),
            issuedAt: $tokenPair->getIssuedAt(),
            accessTokenExpiresAt: $tokenPair->getAccessTokenExpiresAt(),
            refreshTokenExpiresAt: $tokenPair->getRefreshTokenExpiresAt(),
        );
    }

    public function toArray(): array
    {
        return [
            'token_type' => $this->tokenType,
            'access_token' => $this->accessToken,
            'expires_in' => $this->expiresIn,
            'refresh_token' => $this->refreshToken,
            'refresh_expires_in' => $this->refreshExpiresIn,
            'scope' => $this->scope,
            'issued_at' => $this->issuedAt->format(DATE_ATOM),
            'expires_at' => $this->accessTokenExpiresAt->format(DATE_ATOM),
            'refresh_expires_at' => $this->refreshTokenExpiresAt->format(DATE_ATOM),
        ];
    }
}
