<?php

namespace App\Domain\Auth;

use DateTimeImmutable;
use InvalidArgumentException;

class TokenPair
{
    private string $accessToken;
    private string $refreshToken;
    private DateTimeImmutable $issuedAt;
    private DateTimeImmutable $accessTokenExpiresAt;
    private DateTimeImmutable $refreshTokenExpiresAt;
    private string $tokenType;
    /**
     * @var string[]
     */
    private array $scopes;

    /**
     * @param string[] $scopes
     */
    public function __construct(
        string $accessToken,
        string $refreshToken,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $accessTokenExpiresAt,
        DateTimeImmutable $refreshTokenExpiresAt,
        string $tokenType = 'Bearer',
        array $scopes = []
    ) {
        if ($issuedAt >= $accessTokenExpiresAt) {
            throw new InvalidArgumentException('Access token expiration must be greater than issued at.');
        }

        if ($issuedAt >= $refreshTokenExpiresAt) {
            throw new InvalidArgumentException('Refresh token expiration must be greater than issued at.');
        }

        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->issuedAt = $issuedAt;
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
        $this->tokenType = $tokenType;
        $this->scopes = $scopes;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getAccessTokenExpiresAt(): DateTimeImmutable
    {
        return $this->accessTokenExpiresAt;
    }

    public function getRefreshTokenExpiresAt(): DateTimeImmutable
    {
        return $this->refreshTokenExpiresAt;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getExpiresIn(): int
    {
        return $this->accessTokenExpiresAt->getTimestamp() - $this->issuedAt->getTimestamp();
    }

    public function getRefreshExpiresIn(): int
    {
        return $this->refreshTokenExpiresAt->getTimestamp() - $this->issuedAt->getTimestamp();
    }

    public function withRotatedRefreshToken(string $refreshToken, DateTimeImmutable $refreshExpiresAt): self
    {
        return new self(
            $this->accessToken,
            $refreshToken,
            $this->issuedAt,
            $this->accessTokenExpiresAt,
            $refreshExpiresAt,
            $this->tokenType,
            $this->scopes
        );
    }
}
