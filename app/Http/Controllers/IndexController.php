<?php

namespace App\Http\Controllers;

class IndexController extends \Phalcon\Mvc\Controller
{
    public function index()
    {
        return $this->response->setContent('ok');
    }

    public function version()
    {
        return $this->response
            ->setHeader('X-APP-NAME', env('APP_NAME'))
            ->setStatusCode(200)
            ->setContent(env('APP_VER'));
    }
}
