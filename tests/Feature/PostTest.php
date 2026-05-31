<?php

use App\Models\Post;
use App\Repository\UserRepository;

/*
 * Full HTTP + DB exercise of the paginated posts page. Requires a working
 * sqlite PDO driver; self-skips where it is unavailable (same convention as
 * the other DB-dependent feature tests).
 */

$requiresDatabase = !in_array('sqlite', PDO::getAvailableDrivers(), true);

test('posts index renders a paginated list', function () {
    $user = (new UserRepository())->createUser([
        'name' => \Pest\Faker\fake()->name(),
        'email' => \Pest\Faker\fake()->email(),
        'password' => 'secret',
    ]);

    foreach (range(1, 12) as $i) {
        Post::query(); // ensure class autoloads
        $post = (new Post())->fill([
            'user_id' => $user->id,
            'title' => "投稿 {$i}",
            'body' => 'body',
        ]);
        $post->create();
    }

    $this->get('/posts')
        ->assertOk()
        ->assertSee('投稿一覧')
        ->assertSee('page=2');
})->skip($requiresDatabase, 'sqlite PDO driver unavailable');
