<?php

it('test version api response and status code')
    ->get('/version')
//    ->assertHeader('X-APP-NAME', env('APP_NAME'))
//    ->assertSee(env('APP_VER'))
    ->assertOk();