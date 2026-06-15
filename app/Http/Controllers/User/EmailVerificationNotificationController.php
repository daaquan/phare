<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Concerns\SendsAuthEmails;
use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

class EmailVerificationNotificationController extends Controller
{
    use SendsAuthEmails;

    #[Route('email/verification-notification', methods: ['POST'], middlewares: ['auth', 'throttle'], name: 'verification.send')]
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user !== null && method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return $this->response->redirect('/dashboard');
        }

        if ($user instanceof User) {
            $this->sendVerificationEmail($user);
        }

        $this->flashSession->success('認証メールを再送しました。');

        return $this->response->redirect('/user/verify-email');
    }
}
