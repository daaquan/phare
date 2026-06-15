<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Concerns\SendsAuthEmails;
use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

class ProfileController extends Controller
{
    use SendsAuthEmails;

    #[Route('profile', middlewares: ['auth', 'verified'], name: 'settings.profile')]
    public function edit(Request $request)
    {
        return Inertia::render('settings/Profile');
    }

    #[Route('profile', methods: ['PATCH'], middlewares: ['auth', 'verified'], name: 'settings.profile.update')]
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $name = trim((string)$request->get('name'));
        $email = trim((string)$request->get('email'));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set('errors', ['name' => '名前とメールアドレスを正しく入力してください。']);

            return $this->response->redirect('/settings/profile');
        }

        $emailChanged = $email !== (string)$user->email;

        $user->name = $name;
        $user->email = $email;

        if ($emailChanged) {
            // Changing the email un-verifies the account and re-sends the link.
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $this->sendVerificationEmail($user);
        }

        $this->flashSession->success('プロフィールを更新しました。');

        return $this->response->redirect('/settings/profile');
    }
}
