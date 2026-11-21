<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * The link is signed (HMAC over id|email|expires, see User::verificationHash)
     * and only verifies the signed-in user: the {id} in the path must be theirs.
     */
    #[Route('verify-email/{id}/{expires}/{hash}', middlewares: ['auth'], name: 'verification.verify')]
    public function verify(Request $request, string $id, string $expires, string $hash)
    {
        $user = Auth::user();

        if (!$user instanceof User || !$user->hasValidVerificationSignature($id, $expires, $hash)) {
            $this->flashSession->error('That verification link is invalid or has expired. Request a new one.');

            return $this->response->redirect('/user/verify-email');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->response->redirect('/dashboard?verified=1');
    }
}
