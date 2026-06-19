<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WebAuthn\WebAuthnService;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

/**
 * Passkey (WebAuthn) login. Uses a username-less discoverable credential and logs
 * in the owning user once the assertion verifies. A passkey is a strong factor, so
 * password and two-factor are skipped. The frontend calls this with fetch and reads JSON.
 */
class PasskeyLoginController extends Controller
{
    #[Route('passkeys/options', methods: ['POST'], middlewares: ['guest'], name: 'passkeys.login.options')]
    public function options(Request $request)
    {
        $json = (new WebAuthnService())->requestOptions();
        $this->session->set('passkey.request', $json);

        return $this->rawJson($json);
    }

    #[Route('passkeys/login', methods: ['POST'], middlewares: ['guest', 'throttle'], name: 'passkeys.login.store')]
    public function login(Request $request)
    {
        $optionsJson = (string)$this->session->get('passkey.request');
        $this->session->remove('passkey.request');
        if ($optionsJson === '') {
            return $this->json(['message' => 'Your session expired. Please try again.'], 422);
        }

        $body = $request->getJsonRawBody(true) ?: [];
        $response = $body['response'] ?? null;
        if (!is_array($response)) {
            return $this->json(['message' => 'Malformed request.'], 422);
        }

        try {
            $user = (new WebAuthnService())->verifyAssertion($optionsJson, (string)json_encode($response));
        } catch (\Throwable $e) {
            $user = null;
        }

        if (!$user instanceof User) {
            return $this->json(['message' => 'Passkey login failed.'], 422);
        }

        Auth::login($user);

        return $this->json(['ok' => true, 'redirect' => '/dashboard']);
    }
}
