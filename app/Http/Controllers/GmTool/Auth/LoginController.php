<?php

namespace App\Http\Controllers\GmTool\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GmTool\LoginRequest;
use Phox\Attributes\Route;
use Phox\Attributes\RoutePrefix;

#[RoutePrefix('auth')]
class LoginController extends Controller
{
    #[Route('login')]
    public function index()
    {
        return view('auth.login');
    }

    #[Route('login', methods: ['POST'], name: 'store')]
    public function store(LoginRequest $request)
    {
        return redirect('/');
    }
}
