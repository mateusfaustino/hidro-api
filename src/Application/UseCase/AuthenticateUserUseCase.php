<?php

namespace App\Application\UseCase;

use App\Application\DTO\LoginRequestDTO;
use App\Application\DTO\TokenPairDTO;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\TokenManagerInterface;
use App\Domain\Users\UsersRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticateUserUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TokenManagerInterface $tokenManager,
    ) {
    }

    public function execute(LoginRequestDTO $request, ?string $ipAddress = null, ?string $userAgent = null): TokenPairDTO
    {
        $user = $this->usersRepository->findByEmail($request->email);

        if ($user === null) {
            throw new InvalidCredentialsException('User not found.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $request->password)) {
            throw new InvalidCredentialsException('Invalid password.');
        }

        $tokenPair = $this->tokenManager->issueTokenPair($user, $ipAddress, $userAgent);

        return TokenPairDTO::fromTokenPair($tokenPair);
    }
}
