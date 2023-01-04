<?php

namespace App;

use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

class Kernel
{
    private Application $app;

    private MicroCollection $router;

    /**
     * Create a new HTTP kernel instance.
     *
     * @return void
     */
    public function __construct(Application $app, $router)
    {
        $this->app = $app;
        $this->router = $router;
    }

    public function handle(Request $request)
    {
        $this->app->mount($this->router);

        return $this->app->handle($request->getUri());
    }

    public function terminate(Request $request, Response $response)
    {
    }
}