<?php

namespace App\Contracts\Repository;

use App\Models\Game\User;

interface UserContract
{
    public function getUserById(int $id): User|null;

    public function getUserByPublicId(string $publicId): User|null;

    public function createUser(array $data): User;

    public function newNonce(): string;
}