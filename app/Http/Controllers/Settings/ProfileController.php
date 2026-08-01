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
            $this->session->set('errors', ['name' => 'Enter a valid name and email address.']);

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

        $this->flashSession->success('Profile updated.');

        return $this->response->redirect('/settings/profile');
    }

    #[Route('profile', methods: ['DELETE'], middlewares: ['auth'], name: 'settings.profile.destroy')]
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        // Laravel parity: account deletion requires re-entering the password.
        $password = (string)$request->get('password');
        if (!Auth::attempt(['id' => $user->id, 'password' => $password])) {
            $this->session->set('errors', ['password' => 'That password is not correct.']);

            return $this->response->redirect('/settings/profile');
        }

        Auth::logout();
        $user->delete();

        $this->flashSession->success('Account deleted.');

        return $this->response->redirect('/');
    }
}
