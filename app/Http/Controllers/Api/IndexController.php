<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Phox\Foundation\Http\ResponseStatusCode;

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

    public function store()
    {
        return response('created', ResponseStatusCode::CREATED);
    }

    public function update(int $id)
    {
        return response('updated', ResponseStatusCode::OK);
    }

    public function destroy(int $id)
    {
        return response('deleted', ResponseStatusCode::NO_CONTENT);
    }
}
