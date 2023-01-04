<?php

declare(strict_types=1);

namespace App;

use Phalcon\Di;
use Phalcon\Mvc\Micro as App;

class Application
{
    protected App $app;

    protected Di\Di $di;

    protected string $basePath;

    /**
     * @param  string  $basePath  Application directory full path
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        $this->di = new Di\FactoryDefault();
        $this->di->set('app', $this);

        $this->app = $this->createApplication();
    }

    public function handle($uri)
    {
        return $this->app->handle($uri);
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function mount($router): void
    {
        $this->app->mount($router);
    }

    public function terminate()
    {
    }

    public function getDi()
    {
        return $this->di;
    }

    protected function createApplication()
    {
        return new App($this->di);
    }
}
