<?php

it('test index api response and status code will be 401 when not authenticated')
    ->post('/api')
    ->assertUnauthorized()
    ->assertSee('');

test('test version api response and status code and environment variable in header', function () {
    $response = $this->post('/api/version');
    $response->assertOk()
        ->assertHeader('X-APP-NAME', env('APP_NAME'))
        ->assertSee('dev');
});

dataset('user', [
    [[
        'nonce' => \Pest\Faker\fake()->uuid(),
        'name' => \Pest\Faker\fake()->name(),
        'password' => 'secret',
        'email_verified_at' => \Pest\Faker\fake()->dateTime()->format('Y-m-d H:i:s'),
    ]],
]);

test('test index api response and status code will be ok when authenticated', function ($user) {
    $user['email'] = \Pest\Faker\fake()->email();
    $created = (new \App\Repository\UserRepository())
        ->createUser($user);

    $response = $this->post('/api/auth/login', ['id' => $created->id, 'password' => 'secret']);
    $response->assertOk()->assertSee('user_id');

    $response = $this->post('/api', $response->getJsonContent());
    $response->assertOk()->assertSee('ok');
})->with('user');
