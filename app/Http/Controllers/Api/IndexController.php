<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

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
