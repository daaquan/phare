<?php

namespace App\Http\Controllers\Gmtool;

use App\Http\Controllers\Controller;
use Phox\Attributes\Route;
use Phox\Http\Request;

class IndexController extends Controller
{
    #[Route('/', name: 'index')]
    public function index(Request $request)
    {
        return view('admin.index');
    }
}
