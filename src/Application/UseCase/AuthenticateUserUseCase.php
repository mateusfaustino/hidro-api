<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\LoginRequestDTO;
use App\Application\DTO\TokenPairDTO;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\TokenManagerInterface;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\Email;
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
        try {
            $email = Email::fromString($request->email);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidCredentialsException('Invalid email format: ' . $e->getMessage());
        }

        $user = $this->usersRepository->findByEmail($email);

        if ($user === null) {
            throw new InvalidCredentialsException('Invalid credentials.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $request->password)) {
            throw new InvalidCredentialsException('Invalid credentials.');
        }
        
        // Verifica se o usuário pode fazer login
        if (!$user->canLogin()) {
            throw new InvalidCredentialsException(
                'User account is not active. Current status: ' . $user->getStatus()->value
            );
        }

        $tokenPair = $this->tokenManager->issueTokenPair($user, $ipAddress, $userAgent);
        
        // Registra o login
        $user->recordLogin();
        $this->usersRepository->save($user);

        return TokenPairDTO::fromTokenPair($tokenPair);
    }
}
