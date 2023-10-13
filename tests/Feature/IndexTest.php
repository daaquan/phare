<?php

it('test index api response and status code')
    ->post('/')
    ->assertOk()
    ->assertSee('ok');

test('test version api response and status code and environment variable in header', function () {
    $response = $this->post('/version');
    $response->assertOk()
        ->assertHeader('X-APP-NAME', env('APP_NAME'))
        ->assertSee('dev');
});
