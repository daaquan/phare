<?php

namespace App\Http\Controllers;

use Framework\Foundation\Http\ResponseStatusCode;

class ExampleController extends Controller
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

    public function store()
    {
        return response('created', ResponseStatusCode::CREATED);
    }

    public function update($id)
    {
        return response('updated', ResponseStatusCode::OK);
    }

    public function destroy($id)
    {
        return response('deleted', ResponseStatusCode::NO_CONTENT);
    }
}
