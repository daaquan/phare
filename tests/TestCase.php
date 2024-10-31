<?php

namespace Tests;

use Phox\Foundation\AbstractApplication;
use Phox\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function createApplication(): AbstractApplication
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
