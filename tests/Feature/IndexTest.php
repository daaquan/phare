<?php

it('test version api response and status code')
    ->get('/version')
    ->assertHeader('X-APP-NAME', 'Skeleton')
    ->assertSee('dev')
    ->assertOk();