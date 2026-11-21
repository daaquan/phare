<?php

/*
 * Smoke test for the public welcome page, requested as an Inertia visit so the
 * JSON page object is returned (the HTML shell needs a built Vite manifest,
 * which the test environment does not have). The test previously passed only
 * because an earlier test's X-Inertia header leaked through $_SERVER. No DB
 * access (a guest never triggers a model lookup).
 */

test('welcome page renders the Welcome component for guests', function () {
    $this->get('/', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertSee('"component":"Welcome"');
});
