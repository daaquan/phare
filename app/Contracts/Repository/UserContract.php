<?php

namespace App\Contracts\Repository;

use App\Models\Game\User;

interface UserContract
{
    public function getUserById(int $id): ?User;

    public function getUserByPublicId(string $publicId): ?User;

    public function createUser(array $data): User;
}
