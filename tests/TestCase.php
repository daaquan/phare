<?php

namespace Tests;

use Framework\Foundation\Application;
use Framework\Testing\TestResponse;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  string  $uri
     */
    public function get(string $uri): TestResponse
    {
        $response = $this->createApplication()
            ->handle($uri);
        return new TestResponse($response);
    }

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}