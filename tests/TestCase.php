<?php

namespace Tests;

use App\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->mount(require __DIR__.'/../routes/api.php');
        return $app;
    }

    /**
     * @param  string  $uri
     */
    public function get(string $uri): TestResponse
    {
        $response = $this->createApplication()
            ->handle($uri);
        return new TestResponse($response);
    }
}
