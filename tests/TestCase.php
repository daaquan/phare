<?php

namespace Tests;

use Framework\Contracts\Foundation\Application;
use Framework\Contracts\Http\Kernel;
use Framework\Testing\TestCase as BaseTestCase;
use Framework\Testing\TestResponse;

class TestCase extends BaseTestCase
{
    /**
     * @param  string  $uri
     * @return TestResponse
     */
    public function get(string $uri): TestResponse
    {
        $app = $this->createApplication();
        $kernel = $app->make(Kernel::class);

        $response = $app->handle($uri);
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