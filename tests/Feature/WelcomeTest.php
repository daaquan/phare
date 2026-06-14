<?php

/*
 * Smoke test for the public welcome page. It now renders through Inertia: a
 * normal browser visit returns the root HTML with the embedded page object,
 * which names the Welcome component. No DB access (a guest never triggers a
 * model lookup), so it runs regardless of the sqlite driver.
 */

test('welcome page renders the Welcome component for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Phare')
        ->assertSee('Welcome');
});
