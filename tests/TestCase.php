<?php

namespace Tests;

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return Micro
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->setDi(new FactoryDefault());
        $app->mount(require __DIR__.'/../routes/api.php');
        return $app;
    }

    public function get(string $uri)
    {
        return $this->createApplication()
            ->handle($uri);
    }
}