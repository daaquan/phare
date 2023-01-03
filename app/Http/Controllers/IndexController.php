<?php

namespace App\Http\Controllers;

class IndexController extends \Phalcon\Mvc\Controller
{
    public function index()
    {
        return $this->response->setContent('ok');
    }
}