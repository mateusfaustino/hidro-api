<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\CreateSchoolAdminDTO;
use App\Application\DTO\User\UserResponseDTO;
use App\Domain\Users\Exception\DuplicateEmailException;
use App\Domain\Users\User;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\Phone;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Use Case: Criar Administrador da Escola
 * 
 * Persona: Admin da Escola
 * - Configura mensalidades e acessa relatórios
 * - Gerencia toda a escola
 * - Multi-tenant: Vinculado a uma escola específica
 */
final class CreateSchoolAdminUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * @throws DuplicateEmailException
     */
    public function execute(CreateSchoolAdminDTO $dto): UserResponseDTO
    {
        $email = Email::fromString($dto->email);

        // Verifica se email já existe
        if ($this->usersRepository->emailExists($email)) {
            throw DuplicateEmailException::withEmail($dto->email);
        }

        // Cria Admin da Escola
        $phone = $dto->phone ? Phone::fromString($dto->phone) : null;
        
        $user = User::createSchoolAdmin(
            email: $email,
            name: $dto->name,
            hashedPassword: '',
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
