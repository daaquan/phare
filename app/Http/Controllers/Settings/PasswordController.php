<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Auth\AuthSession;
use App\Support\Auth\CredentialChange;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

class PasswordController extends Controller
{
    #[Route('password', methods: ['PUT'], middlewares: ['auth', 'verified', 'throttle.password'], name: 'settings.password.update')]
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->response->redirect('/user/login');
        }

        $current = (string)$request->get('current_password');
        $password = (string)$request->get('password');
        $confirmation = (string)$request->get('password_confirmation');

        if (strlen($password) < 8) {
            $this->session->set('errors', ['password' => 'The password must be at least 8 characters.']);

            return $this->response->redirect('/settings/security');
        }

        if ($password !== $confirmation) {
            $this->session->set('errors', ['password' => 'The passwords do not match.']);

            return $this->response->redirect('/settings/security');
        }

        // Same lock order as the reset form (email lock, then account lock): the
        // old password is re-checked and reset tokens are revoked inside the
        // critical section, so a reset that validated its token earlier cannot
        // overwrite this change afterwards.
        $email = (string)$user->email;
        $wrongPassword = false;
        try {
            $updated = CredentialChange::withEmailLock($email, function () use ($user, $current, $password, $email, &$wrongPassword): ?User {
                if (!Auth::validate(['id' => $user->id, 'password' => $current])) {
                    $wrongPassword = true;

                    return null;
                }

                // Re-reads the row under a lock and bumps session_version once (signs every other session out).
                $updated = CredentialChange::setPassword($user, $password);
                if ($updated !== null) {
                    app('password.broker')->deleteToken($email);
                }

                return $updated;
            });
        } catch (\RuntimeException $e) {
            $updated = null;
        }

        if ($wrongPassword) {
            $this->session->set('errors', ['current_password' => 'Your current password is not correct.']);

            return $this->response->redirect('/settings/security');
        }

        if ($updated === null) {
            $this->session->set('errors', ['password' => 'Please try again in a moment.']);

            return $this->response->redirect('/settings/security');
        }
        // Keep the request-local Auth user in step with the row (never saved from here).
        $user->session_version = $updated->sessionVersion();

        // This browser moves to a new session id (the old one is deleted server-side).
        $this->session->regenerateId(true);
        AuthSession::stamp($user); // this browser stays signed in

        $this->flashSession->success('Password updated.');

        return $this->response->redirect('/settings/security');
    }
}
