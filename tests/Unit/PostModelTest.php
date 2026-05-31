<?php

use App\Models\Post;
use App\Models\User;

/*
 * Structural (DB-free) checks for the Post model and its relations.
 */

test('post model exposes table, fillable and casts', function () {
    $reflection = new ReflectionClass(Post::class);

    $table = $reflection->getProperty('table');
    $fillable = $reflection->getProperty('fillable');
    $casts = $reflection->getProperty('casts');

    $post = $reflection->newInstanceWithoutConstructor();

    expect($table->getValue($post))->toBe('posts')
        ->and($fillable->getValue($post))->toBe(['user_id', 'title', 'body'])
        ->and($casts->getValue($post))->toMatchArray(['id' => 'int', 'user_id' => 'int']);
});

test('post belongs to a user and user has many posts', function () {
    expect(method_exists(Post::class, 'user'))->toBeTrue()
        ->and(method_exists(User::class, 'posts'))->toBeTrue();
});
