<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Controller;
use Phare\Attributes\Route;
use Phare\Http\Request;

class VerifyEmailController extends Controller
{
    protected UserContract $users;

    public function onConstruct(): void
    {
        $this->users = app(UserContract::class);
    }

    #[Route('verify-email/{id}/{hash}', middlewares: ['auth'], name: 'verification.verify')]
    public function verify(Request $request, string $id, string $hash)
    {
        $user = $this->users->getUserById((int)$id);

        // Laravel-compatible hash check (sha1 of the email). The route is
        // additionally auth-guarded so only the signed-in user can verify.
        if ($user === null || !hash_equals($user->verificationHash(), $hash)) {
            return $this->response->redirect('/auth/verify-email');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->response->redirect('/dashboard?verified=1');
    }
}
