<?php

namespace App\Repository;

use App\Contracts\Repository\UserContract;
use App\Models\User;

class UserRepository implements UserContract
{
    public function getUserById(int $id): ?User
    {
        return User::where('id', $id)->first();
    }

    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getUserByPublicId(string $publicId): ?User
    {
        try {
            $decoded = \ID::decode($publicId);
        } catch (\InvalidArgumentException) {
            return null;
        }

        $id = implode('', $decoded);

        if ($id === '' || !ctype_digit($id) || $id === '0') {
            return null;
        }

        return User::where('id', $id)->first();
    }

    public function createUser(array $data): User
    {
        $user = (new User())->fill($data);

        if (!$user->create()) {
            throw new \RuntimeException('Failed to create user.');
        }

        return $user;
    }
}
