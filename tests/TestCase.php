<?php

namespace Tests;

use Phox\Foundation\Micro as Application;
use Phox\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
