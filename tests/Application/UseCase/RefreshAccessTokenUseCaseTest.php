<?php

namespace App\Tests\Application\UseCase;

use App\Application\DTO\RefreshTokenRequestDTO;
use App\Application\DTO\TokenPairDTO;
use App\Application\UseCase\RefreshAccessTokenUseCase;
use App\Domain\Auth\Exception\InvalidRefreshTokenException;
use App\Domain\Auth\TokenManagerInterface;
use App\Domain\Auth\TokenPair;
use PHPUnit\Framework\TestCase;

class RefreshAccessTokenUseCaseTest extends TestCase
{
    public function testExecuteReturnsTokenPairDto(): void
    {
        $tokenPair = new TokenPair(
            accessToken: 'new.access.jwt',
            refreshToken: 'new-refresh-token',
            issuedAt: new \DateTimeImmutable('2024-01-01T12:00:00Z'),
            accessTokenExpiresAt: new \DateTimeImmutable('2024-01-01T12:15:00Z'),
            refreshTokenExpiresAt: new \DateTimeImmutable('2024-01-08T12:00:00Z'),
            tokenType: 'Bearer',
            scopes: ['ROLE_USER']
        );

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->expects(self::once())
            ->method('rotateRefreshToken')
            ->with('valid-refresh-token', null)
            ->willReturn($tokenPair);

        $useCase = new RefreshAccessTokenUseCase($tokenManager);

        $responseDto = $useCase->execute(new RefreshTokenRequestDTO('valid-refresh-token'));

        self::assertInstanceOf(TokenPairDTO::class, $responseDto);
        self::assertSame('new.access.jwt', $responseDto->accessToken);
        self::assertSame('new-refresh-token', $responseDto->refreshToken);
        self::assertSame(900, $responseDto->expiresIn);
        self::assertSame(604800, $responseDto->refreshExpiresIn);
    }

    public function testExecuteThrowsExceptionForInvalidToken(): void
    {
        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->expects(self::once())
            ->method('rotateRefreshToken')
            ->with('invalid-token', null)
            ->willThrowException(new InvalidRefreshTokenException('Invalid refresh token'));

        $useCase = new RefreshAccessTokenUseCase($tokenManager);

        $this->expectException(InvalidRefreshTokenException::class);

        $useCase->execute(new RefreshTokenRequestDTO('invalid-token'));
    }
}
