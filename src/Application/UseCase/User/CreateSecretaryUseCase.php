<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\CreateSecretaryDTO;
use App\Application\DTO\User\UserResponseDTO;
use App\Domain\Users\Exception\DuplicateEmailException;
use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\Phone;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Use Case: Criar Secretária
 * 
 * RF-01: Staff faz login por email/senha
 * Multi-tenant: Associado a uma escola (school_id)
 */
final class CreateSecretaryUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * @throws DuplicateEmailException
     */
    public function execute(CreateSecretaryDTO $dto): UserResponseDTO
    {
        $email = Email::fromString($dto->email);

        // Verifica se email já existe
        if ($this->usersRepository->emailExists($email)) {
            throw DuplicateEmailException::withEmail($dto->email);
        }

        // Cria usuário Secretária
        $phone = $dto->phone ? Phone::fromString($dto->phone) : null;
        
        $user = User::createSecretary(
            email: $email,
            name: $dto->name,
            hashedPassword: '', // Temporário
            schoolId: $dto->schoolId,
            phone: $phone
        );

        // Hash da senha
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->changePassword($hashedPassword);

        // Persiste
        $this->usersRepository->save($user);

        return UserResponseDTO::fromEntity($user);
    }
}
