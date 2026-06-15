<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

class EmailVerificationPromptController extends Controller
{
    #[Route('verify-email', middlewares: ['auth'], name: 'verification.notice')]
    public function show(Request $request)
    {
        $user = Auth::user();

        if ($user !== null && method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return $this->response->redirect('/dashboard');
        }

        return Inertia::render('auth/VerifyEmail');
    }
}
