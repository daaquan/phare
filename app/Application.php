<?php

declare(strict_types=1);

namespace App;

use Phalcon\Di;
use Phalcon\Mvc\Micro as App;

class Application
{
    protected App $app;

    protected string $basePath;

    /**
     * @param  string  $basePath  Application directory full path
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        $this->app = $this->createApplication();
    }

    /**
     * Run Application
     */
    public function run()
    {
        return $this->handle(
            $_SERVER['REQUEST_URI']
        );
    }

    public function handle($uri)
    {
        return $this->app->handle($uri);
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function mount($router)
    {
        $this->app->mount($router);
    }

    protected function createApplication()
    {
        $di = new Di\FactoryDefault();
        $di->set('app', $this);

        return new App($di);
    }
}
