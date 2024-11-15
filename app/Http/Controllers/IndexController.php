<?php

namespace App\Http\Controllers;

use Phox\Attributes\Route;
use Phox\Http\Request;

class IndexController extends Controller
{
    #[Route('/', middlewares: ['auth'], name: 'index')]
    public function index(Request $request)
    {
        return view('dashboard')
            ->with('title', 'Dashboard');
    }
}
