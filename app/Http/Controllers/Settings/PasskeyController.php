<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use App\Models\User;
use App\Support\WebAuthn\WebAuthnService;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;

/**
 * Register and delete passkeys (WebAuthn credentials). The frontend calls these with
 * fetch and reads the registration response or error as JSON -- not an Inertia visit.
 *
 * ponytail: Laravel also guards these with password.confirm; here auth + verified
 * plus the WebAuthn user gesture at registration time (biometric/PIN) count as the
 * re-authentication. Add password.confirm if you want it stricter.
 */
class PasskeyController extends Controller
{
    #[Route('passkeys/options', methods: ['POST'], middlewares: ['auth', 'verified'], name: 'settings.passkeys.options')]
    public function options(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $json = (new WebAuthnService())->creationOptions($user);
        $this->session->set('passkey.creation', $json);

        return $this->rawJson($json);
    }

    #[Route('passkeys', methods: ['POST'], middlewares: ['auth', 'verified'], name: 'settings.passkeys.store')]
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $optionsJson = (string)$this->session->get('passkey.creation');
        $this->session->remove('passkey.creation');
        if ($optionsJson === '') {
            return $this->json(['message' => 'Your session expired. Please try again.'], 422);
        }

        $body = $request->getJsonRawBody(true) ?: [];
        $response = $body['response'] ?? null;
        if (!is_array($response)) {
            return $this->json(['message' => 'Malformed request.'], 422);
        }

        try {
            (new WebAuthnService())->verifyAndStore(
                $user,
                $optionsJson,
                (string)json_encode($response),
                trim((string)($body['name'] ?? '')),
            );
        } catch (\Throwable $e) {
            return $this->json(['message' => 'Passkey registration failed.'], 422);
        }

        $this->flashSession->success('Passkey registered.');

        return $this->json(['ok' => true]);
    }

    #[Route('passkeys/{id}', methods: ['DELETE'], middlewares: ['auth', 'verified'], name: 'settings.passkeys.destroy')]
    public function destroy(Request $request, string $id)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $passkey = Passkey::findFirst([
            'conditions' => 'id = :id: AND user_id = :user:',
            'bind' => ['id' => (int)$id, 'user' => (int)$user->id],
        ]);

        if ($passkey instanceof Passkey) {
            $passkey->delete();
            $this->flashSession->success('Passkey deleted.');
        }

        return $this->json(['ok' => true]);
    }
}
