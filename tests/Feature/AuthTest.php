<?php

$requiresDatabase = !in_array('sqlite', PDO::getAvailableDrivers(), true);

dataset('user', [
    [[
        'name' => \Pest\Faker\fake()->name(),
        'password' => 'secret123',
        'email_verified_at' => \Pest\Faker\fake()->dateTime()->format('Y-m-d H:i:s'),
    ]],
]);

test('test register api success response create user', function ($user) {
    $user['email'] = \Pest\Faker\fake()->email();

    $this->post('/api/auth/register', $user)
        ->assertOk()
        ->assertSee($user['email']);
})->with('user')->skip($requiresDatabase, 'sqlite PDO driver unavailable');

test('test register api response validation error')
    ->post('/api/auth/register')
    ->assertStatus(422)
    ->assertSee('Field name is required');

test('test login api success response user login', function ($user) {
    $user['email'] = \Pest\Faker\fake()->email();
    $created = (new \App\Repository\UserRepository())
        ->createUser($user);

    $response = $this->post('/api/auth/login', [
        'email' => $user['email'],
        'password' => $user['password'],
    ]);

    $response->assertOk()->assertSee(ID::encode($created['id']));
})->with('user')->skip($requiresDatabase, 'sqlite PDO driver unavailable');

test('test login api response with wrong credentials')
    ->skip($requiresDatabase, 'sqlite PDO driver unavailable')
    ->post('/api/auth/login', ['email' => 'nobody@example.com', 'password' => 'incorrect password'])
    ->assertStatus(401)
    ->assertSee('Unauthorized');

test('test login api response without password input')
    ->post('/api/auth/login', ['email' => 'someone@example.com'])
    ->assertStatus(422)
    ->assertSee('Field password is required');
