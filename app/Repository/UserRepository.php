<?php

namespace App\Repository;

use App\Contracts\Repository\UserContract;
use App\Models\User;
use Phalcon\Mvc\Model\Exception as ModelException;

class UserRepository implements UserContract
{
    public function getUserById(int $id): ?User
    {
        return User::findFirstById($id);
    }

    public function getUserByEmail(string $email): ?User
    {
        return User::findFirstByEmail($email);
    }

    public function getUserByPublicId(string $publicId): ?User
    {
        $id = \ID::decode($publicId);

        return User::findFirstById($id);
    }

    public function createUser(array $data): User
    {
        $user = (new User())->fill($data);

        if ($user->validationHasFailed() || !$user->create()) {
            throw new ModelException(implode("\n", $user->getMessages()));
        }

        return $user;
    }
}
