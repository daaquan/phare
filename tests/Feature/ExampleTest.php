<?php

it('test index api response and status code will be 401 when not authenticated')
    ->post('/api')
    ->assertUnauthorized()
    ->assertSee('');

it('test index api response and status code will be ok when authenticated', function () {
    $response = $this->post('/api/auth/login', ['id' => 1131, 'password' => 'secret']);
    $response->assertOk()->assertSee('user_id');

    $response = $this->post('/api', $response->getJsonContent());
    $response->assertOk()->assertSee('ok');
});

test('test version api response and status code and environment variable in header', function () {
    $response = $this->post('/api/version');
    $response->assertOk()
        ->assertHeader('X-APP-NAME', env('APP_NAME'))
        ->assertSee('dev');
});
