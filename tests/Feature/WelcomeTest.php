<?php

/*
 * Smoke test for the public welcome page. No DB access (a guest never
 * triggers a model lookup), so it runs regardless of the sqlite driver.
 */

test('welcome page renders for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Phare')
        ->assertSee('/auth/login');
});
