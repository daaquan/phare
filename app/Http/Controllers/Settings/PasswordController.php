<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

class PasswordController extends Controller
{
    #[Route('password', methods: ['PUT'], middlewares: ['auth', 'verified'], name: 'settings.password.update')]
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $current = (string)$request->get('current_password');
        $password = (string)$request->get('password');
        $confirmation = (string)$request->get('password_confirmation');

        if (!Auth::validate(['id' => $user->id, 'password' => $current])) {
            $this->session->set('errors', ['current_password' => 'Your current password is not correct.']);

            return $this->response->redirect('/settings/security');
        }

        if (strlen($password) < 8) {
            $this->session->set('errors', ['password' => 'The password must be at least 8 characters.']);

            return $this->response->redirect('/settings/security');
        }

        if ($password !== $confirmation) {
            $this->session->set('errors', ['password' => 'The passwords do not match.']);

            return $this->response->redirect('/settings/security');
        }

        $user->password = $password;
        $user->save();

        // Reset links issued for the old password die with it, and this browser
        // moves to a new session id (the old one is deleted server-side).
        app('password.broker')->deleteToken((string)$user->email);
        $this->session->regenerateId(true);

        $this->flashSession->success('Password updated.');

        return $this->response->redirect('/settings/security');
    }
}
