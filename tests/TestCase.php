<?php

namespace Tests;

use Phare\Foundation\AbstractApplication;
use Phare\Testing\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function createApplication(): AbstractApplication
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
