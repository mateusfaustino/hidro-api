<?php

namespace App\Domain\Users;

interface UsersRepository
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
    public function delete(User $user): void;
}