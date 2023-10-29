<?php

namespace App\Repository;

use App\Contracts\Repository\UserContract;
use App\Models\Game\User;
use App\Models\Global\Mapping;
use Framework\Collections\Str;
use Phalcon\Encryption\Security\Exception as SecurityException;
use Phalcon\Encryption\Security\Random as Hash;
use Phalcon\Mvc\Model\Exception as ModelException;
use Phalcon\Support\Helper\Str\Random;

class UserRepository implements UserContract
{
    public function getUserById(int $id): User|null
    {
        return User::find($id);
    }

    public function getUserByPublicId(string $id): User|null
    {
        //$id = public_id($id);
        return User::findFirstById($id);
    }

    /**
     * @return string
     *
     * @todo duplicates are possible. ttl is not implemented.
     */
    public function newNonce(): string
    {
        try {
            return (new Hash())->hex(16);
        } catch (SecurityException $e) {
            \Log::error($e->getMessage());

            return Str::random(Random::RANDOM_ALNUM, 16);
        }
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