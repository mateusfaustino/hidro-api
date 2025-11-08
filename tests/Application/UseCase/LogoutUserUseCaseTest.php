<?php

namespace App\Tests\Application\UseCase;

use App\Application\DTO\RefreshTokenRequestDTO;
use App\Application\UseCase\LogoutUserUseCase;
use App\Domain\Auth\TokenManagerInterface;
use PHPUnit\Framework\TestCase;

class LogoutUserUseCaseTest extends TestCase
{
    public function testExecuteRevokesTokenAndReturnsTrue(): void
    {
        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->expects(self::once())
            ->method('revokeRefreshToken')
            ->with('refresh-token', null);

        $useCase = new LogoutUserUseCase($tokenManager);

        self::assertTrue($useCase->execute(new RefreshTokenRequestDTO('refresh-token')));
    }
}
