<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function index()
    {
        return response('ok');
    }

    public function version()
    {
        return response(\App::version())
            ->setHeader('X-APP-NAME', config('app.name'));
    }
}
