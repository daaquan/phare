<?php

namespace Tests;

use App\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->mount(require __DIR__.'/../routes/api.php');
        return $app;
    }

    public function get(string $uri)
    {
        return $this->createApplication()
            ->handle($uri);
    }
}