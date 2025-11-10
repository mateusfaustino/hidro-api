<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\CreateGuardianDTO;
use App\Application\DTO\User\UserResponseDTO;
use App\Application\UseCase\UseCaseInterface;
use App\Domain\Users\Exception\DuplicateEmailException;
use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\Phone;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Use Case: Criar Usuário Responsável
 * 
 * RF-01: Responsável faz login por email/telefone
 * Multi-tenant: Associado a uma escola (school_id)
 * Status inicial: PENDING (aguardando aprovação)
 */
final class CreateGuardianUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * @throws DuplicateEmailException
     */
    public function execute(CreateGuardianDTO $dto): UserResponseDTO
    {
        $email = Email::fromString($dto->email);
        $phone = Phone::fromString($dto->phone);

        // Verifica se email já existe
        if ($this->usersRepository->emailExists($email)) {
            throw DuplicateEmailException::withEmail($dto->email);
        }

        // Cria usuário Responsável
        $user = User::createGuardian(
            email: $email,
            phone: $phone,
            name: $dto->name,
            hashedPassword: '',
            schoolId: $dto->schoolId
        );

        // Hash da senha
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->changePassword($hashedPassword);

        // Persiste
        $this->usersRepository->save($user);

        return UserResponseDTO::fromEntity($user);
    }
}
