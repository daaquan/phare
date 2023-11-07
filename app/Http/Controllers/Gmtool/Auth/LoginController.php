<?php

namespace App\Http\Controllers\Gmtool\Auth;

use App\Http\Controllers\Controller;
use Phox\Attributes\Route;
use Phox\Attributes\RoutePrefix;

#[RoutePrefix(prefix: '/auth')]
class LoginController extends Controller
{
    #[Route(pattern: 'login')]
    public function index()
    {
        return view('auth.login');
    }

    #[Route(pattern: 'login', methods: ['POST'], name: 'store')]
    public function store()
    {
        return response(['message' => 'Logged in successfully']);
    }
}
