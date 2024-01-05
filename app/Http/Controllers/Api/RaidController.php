<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class RaidController extends Controller
{
    public function index()
    {
        return response(['message' => 'Hello World']);
    }
}
