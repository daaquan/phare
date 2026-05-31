<?php

namespace App\Contracts\Repository;

use App\Models\User;

interface UserContract
{
    public function getUserById(int $id): ?User;

    public function getUserByEmail(string $email): ?User;

    public function getUserByPublicId(string $publicId): ?User;

    public function createUser(array $data): User;
}
