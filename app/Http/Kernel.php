<?php

namespace App\Http;

use Framework\Foundation\Application;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

class Kernel
{
    private Application $app;

    private Request $request;

    private Response $response;

    /**
     * Create a new HTTP kernel instance.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($uri)
    {
        $this->response = $this->app->handle($uri);

        return $this;
    }

    public function send()
    {
        if (!$this->response->isSent()) {
            $this->response
                ->sendHeaders()
                ->send();
        }
    }

    public function terminate()
    {
        $this->app->terminate();
    }
}