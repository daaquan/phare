<?php

namespace App\Http\Controllers\Webview;

use App\Http\Controllers\Controller;
use Phox\Attributes\Route;

class IndexController extends Controller
{
    #[Route('/')]
    public function index()
    {
        return view('webview.index');
    }
}
