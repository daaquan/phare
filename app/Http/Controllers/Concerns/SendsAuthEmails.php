<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Phare\Mail\HtmlMailable;

/**
 * Builds and sends the auth-related transactional emails. With the `log`
 * mailer these are recorded to storage/logs rather than delivered.
 */
trait SendsAuthEmails
{
    protected function sendVerificationEmail(User $user): void
    {
        $link = $this->appUrl()
            . '/user/verify-email/' . $user->id . '/' . $user->verificationHash();

        $html = '<p>Click the link below to verify your email address.</p>'
            . "<p><a href=\"{$link}\">{$link}</a></p>";

        $mail = (new HtmlMailable($html))
            ->to((string)$user->email, (string)$user->name)
            ->subject('Verify your email address');

        app('mailer')->send($mail);
    }

    protected function sendPasswordResetEmail(string $email, string $token): void
    {
        $link = $this->appUrl()
            . '/user/reset-password/' . $token . '?email=' . urlencode($email);

        $html = '<p>Click the link below to reset your password.</p>'
            . "<p><a href=\"{$link}\">{$link}</a></p>";

        $mail = (new HtmlMailable($html))
            ->to($email)
            ->subject('Reset your password');

        app('mailer')->send($mail);
    }

    private function appUrl(): string
    {
        return rtrim((string)config('app.url', 'http://localhost'), '/');
    }
}
