<?php

it('test version api response and status code')
    ->post('/version')
    ->assertHeader('X-APP-NAME', 'Skeleton')
    ->assertSee('dev')
    ->assertOk();