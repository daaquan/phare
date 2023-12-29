<?php

namespace App;

class ApplicationFactory
{
    public static function createApplication(): \Phox\Foundation\AbstractApplication
    {
        $module = getenv('APP_MODULE');

        if (!$module) {
            throw new \RuntimeException('APP_MODULE is not defined');
        }

        $basePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);

        if ($module === 'api') {
            return new \Phox\Foundation\Micro($basePath);
        }

        return new \Phox\Foundation\Web($basePath);
    }
}
