<?php

it('test index api response and status code will be 401 when not authenticated')
    ->get('/api/index')
    ->assertSee('ok');

test('test version api response and status code and environment variable in header', function () {
    $response = $this->post('/api/version');
    $response->assertOk()
        ->assertHeader('X-APP-NAME', env('APP_NAME'))
        ->assertSee('dev');
});
