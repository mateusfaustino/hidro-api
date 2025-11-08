<?php

namespace App\Tests\Application\UseCase;

use App\Application\DTO\LoginRequestDTO;
use App\Application\DTO\TokenPairDTO;
use App\Application\UseCase\AuthenticateUserUseCase;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\TokenManagerInterface;
use App\Domain\Auth\TokenPair;
use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticateUserUseCaseTest extends TestCase
{
    public function testExecuteReturnsTokenPairDto(): void
    {
        $user = new User('user-1', 'john.doe@example.com', 'John Doe');
        $user->setPassword('hashed-password');

        $usersRepository = $this->createMock(UsersRepository::class);
        $usersRepository->expects(self::once())
            ->method('findByEmail')
            ->with('john.doe@example.com')
            ->willReturn($user);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, 'plain-secret')
            ->willReturn(true);

        $tokenPair = new TokenPair(
            accessToken: 'access.jwt.token',
            refreshToken: 'refresh-token',
            issuedAt: new \DateTimeImmutable('2024-01-01T12:00:00Z'),
            accessTokenExpiresAt: new \DateTimeImmutable('2024-01-01T12:15:00Z'),
            refreshTokenExpiresAt: new \DateTimeImmutable('2024-01-08T12:00:00Z'),
            tokenType: 'Bearer',
            scopes: ['ROLE_USER']
        );

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->expects(self::once())
            ->method('issueTokenPair')
            ->with($user)
            ->willReturn($tokenPair);

        $useCase = new AuthenticateUserUseCase(
            $usersRepository,
            $passwordHasher,
            $tokenManager
        );

        $loginRequest = new LoginRequestDTO('john.doe@example.com', 'plain-secret');

        $responseDto = $useCase->execute($loginRequest);

        self::assertInstanceOf(TokenPairDTO::class, $responseDto);
        self::assertSame('access.jwt.token', $responseDto->accessToken);
        self::assertSame('refresh-token', $responseDto->refreshToken);
        self::assertSame('Bearer', $responseDto->tokenType);
        self::assertSame(['ROLE_USER'], $responseDto->scope);
        self::assertSame(900, $responseDto->expiresIn);
        self::assertSame(604800, $responseDto->refreshExpiresIn);
    }

    public function testExecuteThrowsExceptionWhenUserNotFound(): void
    {
        $usersRepository = $this->createMock(UsersRepository::class);
        $usersRepository->expects(self::once())
            ->method('findByEmail')
            ->with('missing@example.com')
            ->willReturn(null);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $tokenManager = $this->createMock(TokenManagerInterface::class);

        $useCase = new AuthenticateUserUseCase($usersRepository, $passwordHasher, $tokenManager);

        $this->expectException(InvalidCredentialsException::class);

        $useCase->execute(new LoginRequestDTO('missing@example.com', 'secret'));
    }

    public function testExecuteThrowsExceptionWhenPasswordInvalid(): void
    {
        $user = new User('user-1', 'john.doe@example.com', 'John Doe');
        $user->setPassword('hashed-password');

        $usersRepository = $this->createMock(UsersRepository::class);
        $usersRepository->expects(self::once())
            ->method('findByEmail')
            ->with('john.doe@example.com')
            ->willReturn($user);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, 'plain-secret')
            ->willReturn(false);

        $tokenManager = $this->createMock(TokenManagerInterface::class);

        $useCase = new AuthenticateUserUseCase($usersRepository, $passwordHasher, $tokenManager);

        $this->expectException(InvalidCredentialsException::class);

        $useCase->execute(new LoginRequestDTO('john.doe@example.com', 'plain-secret'));
    }
}
