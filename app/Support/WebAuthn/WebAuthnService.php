<?php

namespace App\Support\WebAuthn;

use App\Models\Passkey;
use App\Models\User;
use Cose\Algorithms;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Server-side wrapper around WebAuthn (passkey) handling.
 *
 * The cryptography -- signature and attestation verification -- is delegated to
 * web-auth/webauthn-lib. Unlike TOTP, hand-rolling WebAuthn (CBOR + COSE + EC/RSA
 * signatures) is too risky, so the app only builds options, stores credentials and
 * wires the login.
 */
final class WebAuthnService
{
    private SerializerInterface $serializer;

    private CeremonyStepManagerFactory $ceremonies;

    public function __construct()
    {
        // Accept the "none" attestation format only (platform authenticator passkeys).
        $attestationManager = new AttestationStatementSupportManager([
            new NoneAttestationStatementSupport(),
        ]);

        $this->serializer = (new WebauthnSerializerFactory($attestationManager))->create();

        $this->ceremonies = new CeremonyStepManagerFactory();
        $this->ceremonies->setAttestationStatementSupportManager($attestationManager);
        $this->ceremonies->setAllowedOrigins([$this->origin()]);
    }

    // ------------------------------------------------------------------
    // Registration (attestation)
    // ------------------------------------------------------------------

    /**
     * Build the registration options and return them as JSON. The caller must stash
     * that JSON in the session so the challenge can be matched later.
     */
    public function creationOptions(User $user): string
    {
        $rp = PublicKeyCredentialRpEntity::create($this->rpName(), $this->rpId());

        $userEntity = PublicKeyCredentialUserEntity::create(
            (string)$user->email,
            (string)$user->id,
            (string)$user->name,
        );

        // Exclude existing passkeys so the same authenticator cannot register twice.
        $exclude = [];
        foreach (Passkey::findByUserId((int)$user->id) as $passkey) {
            $exclude[] = PublicKeyCredentialDescriptor::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                self::base64urlDecode((string)$passkey->credential_id),
            );
        }

        $options = PublicKeyCredentialCreationOptions::create(
            $rp,
            $userEntity,
            random_bytes(32),
            [
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_RS256),
            ],
            AuthenticatorSelectionCriteria::create(
                null,
                AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
                AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
            ),
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $exclude,
        );

        return $this->serializer->serialize($options, 'json');
    }

    /**
     * Verify the registration response from the browser and store the credential.
     *
     * @param string $optionsJson The creationOptions JSON stashed in the session
     * @param string $responseJson The PublicKeyCredential JSON returned by the browser
     *
     * @throws \Throwable When verification fails
     */
    public function verifyAndStore(User $user, string $optionsJson, string $responseJson, string $name): Passkey
    {
        /** @var PublicKeyCredentialCreationOptions $options */
        $options = $this->serializer->deserialize(
            $optionsJson,
            PublicKeyCredentialCreationOptions::class,
            'json',
        );

        $credential = $this->serializer->deserialize($responseJson, PublicKeyCredential::class, 'json');
        if (!$credential->response instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException('Invalid attestation response');
        }

        $validator = AuthenticatorAttestationResponseValidator::create(
            $this->ceremonies->creationCeremony(),
        );

        $record = $validator->check($credential->response, $options, $this->rpId());

        $passkey = new Passkey();
        $passkey->fill([
            'user_id' => (int)$user->id,
            'name' => $name !== '' ? $name : 'Passkey',
            'credential_id' => self::base64urlEncode($record->publicKeyCredentialId),
            'data' => $this->serializer->serialize($record, 'json'),
        ]);
        $passkey->save();

        return $passkey;
    }

    // ------------------------------------------------------------------
    // Authentication (assertion)
    // ------------------------------------------------------------------

    /**
     * Build username-less (discoverable) login options and return them as JSON.
     * The caller must stash them in the session.
     */
    public function requestOptions(): string
    {
        $options = PublicKeyCredentialRequestOptions::create(
            random_bytes(32),
            $this->rpId(),
            [], // empty allowCredentials, so discoverable credentials are allowed
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
        );

        return $this->serializer->serialize($options, 'json');
    }

    /**
     * Verify the login assertion and return the owning user, or null on failure.
     *
     * @throws \Throwable When verification fails
     */
    public function verifyAssertion(string $optionsJson, string $responseJson): ?User
    {
        /** @var PublicKeyCredentialRequestOptions $options */
        $options = $this->serializer->deserialize(
            $optionsJson,
            PublicKeyCredentialRequestOptions::class,
            'json',
        );

        $credential = $this->serializer->deserialize($responseJson, PublicKeyCredential::class, 'json');
        if (!$credential->response instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Invalid assertion response');
        }

        $passkey = Passkey::findFirstByCredentialId(self::base64urlEncode($credential->rawId));
        if (!$passkey instanceof Passkey) {
            return null;
        }

        /** @var CredentialRecord $record */
        $record = $this->serializer->deserialize((string)$passkey->data, CredentialRecord::class, 'json');

        $validator = AuthenticatorAssertionResponseValidator::create(
            $this->ceremonies->requestCeremony(),
        );

        $updated = $validator->check(
            $record,
            $credential->response,
            $options,
            $this->rpId(),
            $record->userHandle,
        );

        // Persist the updated signature counter and friends.
        $passkey->data = $this->serializer->serialize($updated, 'json');
        $passkey->last_used_at = date('Y-m-d H:i:s');
        $passkey->save();

        return User::findFirstById((int)$passkey->user_id);
    }

    // ------------------------------------------------------------------
    // Configuration (relying party)
    // ------------------------------------------------------------------

    private function appUrl(): string
    {
        // Passing a string default as env()'s second argument risks it being treated as a
        // callable via is_callable, so fall back with ?: instead.
        return (string)(env('APP_URL') ?: 'http://localhost:8000');
    }

    private function rpId(): string
    {
        return parse_url($this->appUrl(), PHP_URL_HOST) ?: 'localhost';
    }

    private function origin(): string
    {
        $parts = parse_url($this->appUrl());
        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? 'localhost';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return "{$scheme}://{$host}{$port}";
    }

    private function rpName(): string
    {
        return (string)config('app.name', 'Phare');
    }

    // ------------------------------------------------------------------
    // base64url
    // ------------------------------------------------------------------

    public static function base64urlEncode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function base64urlDecode(string $value): string
    {
        return (string)base64_decode(strtr($value, '-_', '+/'));
    }
}
