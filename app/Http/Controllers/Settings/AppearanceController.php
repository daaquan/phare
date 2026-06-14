<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

class AppearanceController extends Controller
{
    #[Route('appearance', middlewares: ['auth', 'verified'], name: 'settings.appearance')]
    public function edit(Request $request)
    {
        // The theme toggle is entirely client-side (localStorage + .dark class);
        // this endpoint only renders the page.
        return Inertia::render('settings/Appearance');
    }
}
