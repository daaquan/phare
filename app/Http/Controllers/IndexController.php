<?php

namespace App\Http\Controllers;

use Phare\Attributes\Route;
use Phare\Http\Request;

class IndexController extends Controller
{
    #[Route('/', name: 'welcome')]
    public function welcome(Request $request)
    {
        return view('welcome')
            ->with('title', __('welcome.title'));
    }

    #[Route('/dashboard', middlewares: ['auth'], name: 'dashboard')]
    public function dashboard(Request $request)
    {
        return view('dashboard')
            ->with('title', 'Dashboard');
    }
}
