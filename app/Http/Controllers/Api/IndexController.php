<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Phalcon\Support\Version;
use Phare\Attributes\Route;

class IndexController extends Controller
{
    #[Route('index', name: 'index')]
    public function index()
    {
        return response(['version' => 'Phalcon ' . (new Version())->get(), 'message' => 'ok']);
    }

    #[Route('version', methods: ['POST'], name: 'version')]
    public function version()
    {
        return response(\App::version())
            ->setHeader('X-APP-NAME', config('app.name'));
    }
}
