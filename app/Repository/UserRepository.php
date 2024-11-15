<?php

namespace App\Repository;

use App\Contracts\Repository\UserContract;
use App\Models\Mapping;
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
        $mapping = new Mapping([
            'shard_id' => random_int(0, config('database.connections.game.shards')->count() - 1),
        ]);
        if ($mapping->validationHasFailed() || !$mapping->create()) {
            throw new ModelException(implode("\n", $mapping->getMessages()));
        }

        $data['id'] = $mapping->id;
        $user = (new User())->fill($data);

        if ($user->validationHasFailed() || !$user->create()) {
            throw new ModelException(implode("\n", $user->getMessages()));
        }

        return $user;
    }
}
