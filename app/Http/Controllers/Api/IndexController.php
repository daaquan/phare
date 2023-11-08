<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Phox\Attributes\Route;
use Phox\Foundation\Http\ResponseStatusCode;

class IndexController extends Controller
{
    #[Route('index')]
    public function index()
    {
        return response('ok');
    }

    #[Route('version')]
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
