<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Phare\Database\Factory;
use Phare\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $userId = $this->firstUserId();

        if ($userId === null) {
            $this->create('users', [
                'name' => 'Seed User',
                'email' => 'seed@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $userId = $this->firstUserId();
        }

        (new Factory($this->app))
            ->for(Post::class)
            ->count(25)
            ->create(['user_id' => $userId]);
    }

    private function firstUserId(): ?int
    {
        foreach (User::query()->orderByDesc('id')->get() as $user) {
            return (int)$user->id;
        }

        return null;
    }
}
