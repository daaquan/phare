<?php

namespace App\Http\Controllers\Gmtool;

use App\Http\Controllers\Controller;
use Phox\Attributes\Route;

//#[\Phox\Attributes\RoutePrefix(middlewares: ['auth.gmtool'])]
class IndexController extends Controller
{
    #[Route('/', name: 'index')]
    public function index()
    {
        return view('admin.index');
    }
}
