<?php

namespace App\Repository;

use App\Contracts\Repository\UserContract;
use App\Models\Game\User;
use App\Models\Global\Mapping;
use Phalcon\Mvc\Model\Exception as ModelException;
use Random\RandomException;

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

    /**
     * @see https://developer.android.com/google/play/integrity/verdict#nonce
     */
    public function newNonce(): string
    {
        try {
            $binary = random_bytes(16);
        } catch (\Exception $e) {
            throw new RandomException($e->getMessage());
        }

        return base64_encode(bin2hex($binary));
    }

    public function createUser(array $data): User
    {
        $mapping = new Mapping([
            // @todo シャードIDをクライアントから取得またはmappingから取得する
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
