<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Application\DTO\User\UpdateUserDTO;
use App\Application\DTO\User\UserResponseDTO;
use App\Domain\Users\Exception\UserNotFoundException;
use App\Domain\Users\UsersRepository;
use App\Domain\Users\ValueObject\Email;
use App\Domain\Users\ValueObject\Phone;
use App\Domain\Users\ValueObject\UserId;

/**
 * Use Case: Atualizar dados do Usuário
 */
final class UpdateUserUseCase
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function execute(UpdateUserDTO $dto): UserResponseDTO
    {
        $id = UserId::fromString($dto->userId);
        $user = $this->usersRepository->findById($id);

        if (!$user) {
            throw UserNotFoundException::withId($dto->userId);
        }

        // Atualiza nome e telefone
        if ($dto->name || $dto->phone) {
            $phone = $dto->phone ? Phone::fromString($dto->phone) : $user->getPhone();
            $user->updateProfile($dto->name ?? $user->getName(), $phone);
        }

        // Atualiza email se fornecido
        if ($dto->email) {
            $newEmail = Email::fromString($dto->email);
            $user->changeEmail($newEmail);
        }

        $this->usersRepository->save($user);

        return UserResponseDTO::fromEntity($user);
    }
}
