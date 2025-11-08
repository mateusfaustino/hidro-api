<?php

namespace App\Tests\Application\Service;

use App\Application\DTO\TokenPairDTO;
use App\Domain\Auth\TokenPair;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    public function testTokenPairDtoConversion(): void
    {
        $tokenPair = new TokenPair(
            accessToken: 'access-token',
            refreshToken: 'refresh-token',
            issuedAt: new \DateTimeImmutable('2024-01-01T12:00:00Z'),
            accessTokenExpiresAt: new \DateTimeImmutable('2024-01-01T12:15:00Z'),
            refreshTokenExpiresAt: new \DateTimeImmutable('2024-01-08T12:00:00Z'),
            tokenType: 'Bearer',
            scopes: ['ROLE_USER']
        );

        $dto = TokenPairDTO::fromTokenPair($tokenPair);

        self::assertSame('access-token', $dto->accessToken);
        self::assertSame('refresh-token', $dto->refreshToken);
        self::assertSame('Bearer', $dto->tokenType);
        self::assertSame(['ROLE_USER'], $dto->scope);
        self::assertSame(900, $dto->expiresIn);
        self::assertSame(604800, $dto->refreshExpiresIn);
    }
}
