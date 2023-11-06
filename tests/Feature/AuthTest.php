<?php

beforeEach(function () {
    //DB::beginTransaction();
});

afterEach(function () {
    //DB::rollBack();
});

test('test nonce api response create nonce')
    ->with([
        'device_id' => $device_id = \Pest\Faker\fake()->uuid(),
        'password' => $password = 'secret',
    ])
    ->post('/auth/login/nonce', compact('device_id'))
    ->assertStatus(200);

test('test nonce api response validation error')
    ->post('/auth/login/nonce')
    ->assertStatus(400)
    ->assertSee('Field device_id is required');

dataset('user', [
    [[
        'nonce' => \Pest\Faker\fake()->uuid(),
        'name' => \Pest\Faker\fake()->name(),
        'password' => 'secret',
        'email_verified_at' => \Pest\Faker\fake()->dateTime()->format('Y-m-d H:i:s'),
    ]]
]);

test('test register api success response create user', function ($user) {
    $user['email'] = \Pest\Faker\fake()->email();

    $this->post('/auth/register', $user)
        ->assertOk()
        ->assertSee($user['email']);
})->with('user');

test('test register api response validation error')
    ->post('/auth/register')
    ->assertStatus(400)
    ->assertSee('Field nonce is required');

test('test login api success response user login', function ($user) {
    $user['email'] = \Pest\Faker\fake()->email();
    $created = (new \App\Repository\UserRepository())
        ->createUser($user);

    $response = $this->post('/auth/login', [
        'id' => $created['id'],
        'password' => $user['password'],
    ]);
    $response->assertOk()->assertSee(ID::encode(str_split($created['id'])));
})->with('user');

test('test login api response with string id input')
    ->post('/auth/login', ['id' => 1, 'password' => 'incorrect password'])
    ->assertStatus(401)
    ->assertSee('Unauthorized');

test('test login api response without password input')
    ->post('/auth/login')
    ->assertStatus(400)
    ->assertSee('Field password is required');
