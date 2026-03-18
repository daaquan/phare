<?php

dataset('user', [
    [[
        'name' => \Pest\Faker\fake()->name(),
        'password' => 'secret',
        'email_verified_at' => \Pest\Faker\fake()->dateTime()->format('Y-m-d H:i:s'),
    ]],
]);

test('test register api success response create user', function ($user) {
    $user['email'] = \Pest\Faker\fake()->email();

    $this->post('/api/auth/register', $user)
        ->assertOk()
        ->assertSee($user['email']);
})->with('user');

test('test register api response validation error')
    ->post('/api/auth/register')
    ->assertStatus(400)
    ->assertSee('Field name is required');

test('test login api success response user login', function ($user) {
    $user['email'] = \Pest\Faker\fake()->email();
    $created = (new \App\Repository\UserRepository())
        ->createUser($user);

    $response = $this->post('/api/auth/login', [
        'id' => $created['id'],
        'password' => $user['password'],
    ]);

    $response->assertOk()->assertSee(ID::encode($created['id']));
})->with('user');

test('test login api response with string id input')
    ->post('/api/auth/login', ['id' => 1, 'password' => 'incorrect password'])
    ->assertStatus(401)
    ->assertSee('Unauthorized');

test('test login api response without password input')
    ->post('/api/auth/login')
    ->assertStatus(400)
    ->assertSee('Field password is required');
