<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use App\Models\User;
use App\Support\TwoFactor\Totp;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

/**
 * Security settings page. Following the Laravel 13 starter kit, password changes,
 * two-factor and passkeys live on one screen; the individual update endpoints stay
 * in PasswordController / TwoFactorAuthenticationController / PasskeyController.
 */
class SecurityController extends Controller
{
    #[Route('security', middlewares: ['auth', 'verified'], name: 'settings.security')]
    public function show(Request $request)
    {
        $user = Auth::user();

        $enabled = $user instanceof User && $user->hasTwoFactorEnabled();
        $pending = $user instanceof User
            && $user->two_factor_secret !== null
            && !$enabled;

        $twoFactor = [
            'enabled' => $enabled,
            'pending' => $pending,
        ];

        // Expose the manual key / otpauth URI only while a secret is unconfirmed.
        // $pending already implies $user is a User with a non-null two_factor_secret.
        if ($pending) {
            $secret = (string)$user->two_factor_secret;
            $twoFactor['secret'] = $secret;
            $twoFactor['otpauthUri'] = Totp::provisioningUri(
                $secret,
                (string)$user->email,
                (string)config('app.name', 'Phare'),
            );
        }

        // $enabled likewise implies $user is a User.
        if ($enabled) {
            $twoFactor['recoveryCodes'] = $user->recoveryCodes();
        }

        return Inertia::render('settings/security', [
            'twoFactor' => $twoFactor,
            'passkeys' => $this->passkeys($user),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function passkeys(?object $user): array
    {
        if (!$user instanceof User) {
            return [];
        }

        $passkeys = [];
        foreach (Passkey::where('user_id', (int)$user->id)->get() as $passkey) {
            $passkeys[] = [
                'id' => (int)$passkey->id,
                'name' => (string)$passkey->name,
                'last_used_at' => $passkey->last_used_at,
                'created_at' => $passkey->created_at,
            ];
        }

        return $passkeys;
    }
}
