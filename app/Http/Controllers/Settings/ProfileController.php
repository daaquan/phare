<?php

namespace App\Http\Controllers\Settings;

use App\Contracts\Repository\UserContract;
use App\Http\Controllers\Concerns\SendsAuthEmails;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auth\AuthSession;
use App\Support\Auth\CredentialChange;
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

    // Changing the email hands over password-reset control, so it needs a fresh password.
    #[Route('profile', methods: ['PATCH'], middlewares: ['auth', 'verified', 'password.confirm'], name: 'settings.profile.update')]
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

        if ($emailChanged && app(UserContract::class)->getUserByEmail($email) !== null) {
            $this->session->set('errors', ['email' => 'That email address is already taken.']);

            return $this->response->redirect('/settings/profile');
        }

        $oldEmail = (string)$user->email;

        try {
            // Same lock as reset-link issuance: no token can be written for the old
            // address after it was revoked here.
            CredentialChange::withEmailLock($oldEmail, function () use ($user, $name, $email, $emailChanged, $oldEmail): void {
                // The address was read before the lock: re-read, and refuse when a
                // concurrent request already moved the account off the locked address.
                $fresh = User::findFirst((int)$user->id);
                if (!$fresh instanceof User || (string)$fresh->email !== $oldEmail) {
                    throw new \RuntimeException('account changed while waiting for the lock');
                }

                $user->name = $name;
                $user->email = $email;

                if ($emailChanged) {
                    // Changing the email un-verifies the account and re-sends the link.
                    $user->email_verified_at = null;
                }

                $user->save();

                if ($emailChanged) {
                    // Reset tokens are keyed by email: revoke any still-valid one on the old address.
                    app('password.broker')->deleteToken($oldEmail);
                }
            });
        } catch (\RuntimeException $e) {
            $this->session->set('errors', ['email' => 'Please try again in a moment.']);

            return $this->response->redirect('/settings/profile');
        }

        if ($emailChanged) {
            $this->sendVerificationEmail($user);
        }

        $this->flashSession->success('Profile updated.');

        return $this->response->redirect('/settings/profile');
    }

    #[Route('profile', methods: ['DELETE'], middlewares: ['auth', 'throttle.password'], name: 'settings.profile.destroy')]
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        // Laravel parity: account deletion requires re-entering the password.
        $password = (string)$request->get('password');
        if (!Auth::validate(['id' => $user->id, 'password' => $password])) {
            $this->session->set('errors', ['password' => 'That password is not correct.']);

            return $this->response->redirect('/settings/profile');
        }

        $email = (string)$user->email;

        try {
            CredentialChange::withEmailLock($email, function () use ($user, $email): void {
                $fresh = User::findFirst((int)$user->id);
                if (!$fresh instanceof User || (string)$fresh->email !== $email) {
                    throw new \RuntimeException('account changed while waiting for the lock');
                }

                $user->delete();
                // Revoke outstanding reset tokens so a new account on this email cannot reuse them.
                app('password.broker')->deleteToken($email);
            });
        } catch (\RuntimeException $e) {
            $this->session->set('errors', ['password' => 'Please try again in a moment.']);

            return $this->response->redirect('/settings/profile');
        }
        AuthSession::logout();

        $this->flashSession->success('Account deleted.');

        return $this->response->redirect('/');
    }
}
